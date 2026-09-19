<?php

namespace App\Http\Controllers\Responsable\Ppe;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Operation;
use App\Services\Audit\Consignateur;
use App\Services\Ppe\GenerateurListePpe;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Liste des PPE de l'agence du responsable, export PDF/Excel et détail des opérations. */
class PpeController extends Controller
{
    public function index(Request $request, GenerateurListePpe $generateur): View
    {
        [$du, $au] = $this->periode($request);
        $agence = $request->user('agent')->agence;

        return view('responsable.ppe.index', [
            'clients' => $generateur->clientsPpe($agence, $du, $au),
            'du' => $du,
            'au' => $au,
        ]);
    }

    public function exporter(Request $request, string $format, GenerateurListePpe $generateur)
    {
        [$du, $au] = $this->periode($request);
        $agent = $request->user('agent');
        $clients = $generateur->clientsPpe($agent->agence, $du, $au);

        Consignateur::enregistrer('agent', $agent->id, 'export_liste_ppe', 'agence', (string) $agent->agence_id, ['format' => $format]);

        if ($format === 'pdf') {
            return Pdf::loadView('pdf.liste-ppe', ['agence' => $agent->agence, 'clients' => $clients, 'genereLe' => now()])
                ->setPaper('a4', 'landscape')
                ->download('liste-ppe-'.now()->format('Ymd').'.pdf');
        }

        $feuille = new Spreadsheet;
        $ws = $feuille->getActiveSheet()->setTitle('PPE');
        $ws->fromArray(['Client', 'Type', 'Statut PPE', 'Déclarée à l\'adhésion', 'Pièces justificatives', 'Fiche créée le'], null, 'A1');
        $ligne = 2;
        foreach ($clients as $c) {
            $ws->fromArray([
                $c->nomAffichage(),
                $c->type->value === 'personne_morale' ? 'Personne morale' : 'Personne physique',
                $c->statut_ppe->libelle(),
                $c->ppe_declare ? 'Oui' : 'Non',
                $c->documents_ppe_count,
                $c->created_at->format('d/m/Y'),
            ], null, 'A'.$ligne++);
        }

        return new StreamedResponse(fn () => (new Xlsx($feuille))->save('php://output'), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="liste-ppe-'.now()->format('Ymd').'.xlsx"',
        ]);
    }

    public function detail(Request $request, Client $client): View
    {
        $agent = $request->user('agent');
        abort_unless(
            Client::deLAgence((int) $agent->agence_id)->whereKey($client->id)->where(fn ($q) => $q->where('ppe_declare', true)->orWhere('statut_ppe', '!=', 'non_ppe'))->exists(),
            404,
        );
        [$du, $au] = $this->periode($request);

        $operations = Operation::query()
            ->whereHas('compte', fn ($q) => $q->where('client_id', $client->id))
            ->when($du, fn ($q) => $q->whereDate('effectuee_le', '>=', $du))
            ->when($au, fn ($q) => $q->whereDate('effectuee_le', '<=', $au))
            ->with('agence')
            ->orderByDesc('effectuee_le')
            ->get();

        Consignateur::enregistrer('agent', $agent->id, 'consultation', 'client', $client->id);

        return view('responsable.ppe.detail', [
            'client' => $client->load('documentsPpe'),
            'operations' => $operations,
            'du' => $du,
            'au' => $au,
        ]);
    }

    /** @return array{0: ?string, 1: ?string} */
    private function periode(Request $request): array
    {
        $v = $request->validate(['du' => ['nullable', 'date'], 'au' => ['nullable', 'date', 'after_or_equal:du']]);

        return [$v['du'] ?? null, $v['au'] ?? null];
    }
}
