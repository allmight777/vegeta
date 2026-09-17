<?php

namespace Database\Seeders;

use App\Enums\RoleAgent;
use App\Models\Admin;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use App\Services\Securite\IndexAveugle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Comptes de démonstration — uniquement si CIF_DEMO=true. Mots de passe générés
 * aléatoirement et affichés une seule fois en console, jamais commités en clair.
 */
class DemoComptesSeeder extends Seeder
{
    public function run(IndexAveugle $indexAveugle): void
    {
        if (! config('cif_demo.actif')) {
            $this->command?->warn('CIF_DEMO=false : comptes de démonstration non créés.');

            return;
        }

        $alpha = Reseau::where('code', 'ALPHA')->firstOrFail();
        $dassa = Agence::where('reseau_id', $alpha->id)->where('code', 'DASSA')->firstOrFail();

        $this->command?->info('Comptes de démonstration créés — identifiants ci-dessous (à ne pas committer) :');

        $this->creerAdmin($indexAveugle, 'admin.plateforme@cif-empreinte.demo', null, 'Admin CIF (plateforme)');
        $this->creerAdmin($indexAveugle, 'admin.alpha@cif-empreinte.demo', $alpha->id, 'Admin réseau Alpha');

        $this->creerAgent($indexAveugle, 'GUI-0001', $dassa->id, RoleAgent::Guichet, 'Agent guichet');
        $this->creerAgent($indexAveugle, 'LBC-0001', $dassa->id, RoleAgent::ResponsableLbcft, 'Responsable LBC/FT');
        $this->creerAgent($indexAveugle, 'DIR-0001', $dassa->id, RoleAgent::Direction, 'Direction');
    }

    private function creerAdmin(IndexAveugle $indexAveugle, string $email, ?int $reseauId, string $nom): void
    {
        $idx = $indexAveugle->calculer($email, 'email');

        if (Admin::where('email_idx', $idx)->exists()) {
            return;
        }

        $motDePasse = Str::password(16);

        Admin::create([
            'reseau_id' => $reseauId,
            'nom' => $nom,
            'email' => $email,
            'mot_de_passe' => Hash::make($motDePasse),
            'actif' => true,
        ]);

        $this->command?->line("  [admin] {$email} / {$motDePasse}");
    }

    private function creerAgent(IndexAveugle $indexAveugle, string $matricule, int $agenceId, RoleAgent $role, string $nom): void
    {
        $idx = $indexAveugle->calculer($matricule, 'matricule');

        if (Agent::where('matricule_idx', $idx)->exists()) {
            return;
        }

        $motDePasse = Str::password(16);

        Agent::create([
            'agence_id' => $agenceId,
            'nom' => $nom,
            'matricule' => $matricule,
            'mot_de_passe' => Hash::make($motDePasse),
            'role' => $role,
            'actif' => true,
        ]);

        $this->command?->line("  [agent {$role->value}] {$matricule} / {$motDePasse}");
    }
}
