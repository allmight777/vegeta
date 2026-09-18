<?php

namespace App\Console\Commands\Rapports;

use App\Enums\RoleAgent;
use App\Mail\RapportJournalierMail;
use App\Models\Agent;
use App\Models\RapportJournalier;
use App\Services\Rapports\GenerateurRapportJournalier;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EnvoyerRapportsQuotidiens extends Command
{
    protected $signature = 'rapports:envoyer-quotidiens {--date= : Date du rapport (YYYY-MM-DD)}';
    protected $description = 'Génère et envoie le rapport quotidien sécurisé à chaque responsable d’agence.';

    public function handle(GenerateurRapportJournalier $generateur): int
    {
        $date = CarbonImmutable::parse($this->option('date') ?: today()->toDateString());

        Agent::where('role', RoleAgent::ResponsableAgence->value)
            ->whereNotNull('email')
            ->with('agence')
            ->each(function (Agent $responsable) use ($generateur, $date): void {
                if ($responsable->agence === null || RapportJournalier::where('agence_id', $responsable->agence_id)
                    ->whereDate('date_debut', $date)->whereDate('date_fin', $date)->whereNotNull('envoye_le')->exists()) {
                    return;
                }

                $rapport = $generateur->generer($responsable->agence, $date, $date);
                $code = Str::password(16, true, true, true, false);
                $rapport->update([
                    'destinataire_email' => $responsable->email,
                    'code_acces_hash' => Hash::make($code),
                    'jeton_partage' => Str::random(64),
                    'expire_le' => now()->addDays(7),
                    'envoye_le' => now(),
                ]);
                Mail::to($responsable->email)->send(new RapportJournalierMail($rapport->fresh(), $code));
                $this->info("Rapport envoyé à {$responsable->nom}.");
            });

        return self::SUCCESS;
    }
}
