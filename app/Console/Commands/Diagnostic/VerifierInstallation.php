<?php

namespace App\Console\Commands\Diagnostic;

use App\Models\Client;
use App\Models\EntreeListe;
use App\Services\Securite\GestionnaireCles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Contrôle de pré-vol, en lecture seule : vérifie que la machine sur laquelle on
 * s'apprête à démontrer le produit est réellement prête. Née d'un incident réel —
 * `.env.example` livrait `CLE_CIF_DEMO` vide alors que le README affirmait le
 * contraire, et l'installation documentée échouait au premier seeder.
 *
 * Chaque contrôle en échec affiche la commande exacte qui le répare : jamais un
 * diagnostic sans remède.
 */
class VerifierInstallation extends Command
{
    protected $signature = 'installation:verifier';

    protected $description = 'Vérifie que l\'installation est complète et prête pour la démonstration';

    /** @var array<int, array{0: string, 1: bool, 2: string}> */
    private array $controles = [];

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>CIF-Empreinte — contrôle de pré-vol</>');
        $this->newLine();

        $this->verifierPhp();
        $this->verifierExtensions();
        $this->verifierDependances();
        $this->verifierClesApplicatives();
        $this->verifierBaseDeDonnees();
        $this->verifierDonneesDemonstration();
        $this->verifierModeDemonstration();

        return $this->afficherSynthese();
    }

    private function verifierPhp(): void
    {
        $this->controle(
            'PHP ≥ 8.3 ('.PHP_VERSION.')',
            version_compare(PHP_VERSION, '8.3.0', '>='),
            'Installer PHP 8.3 ou supérieur.'
        );
    }

    /**
     * Cohérence entre composer.lock et ce qui est réellement installé dans vendor/.
     *
     * Né d'un incident réel : 14 paquets déclarés (maatwebsite/excel, mpdf, l'OCR…)
     * étaient absents du vendor/ livré. L'application démarre, les tests passent —
     * et la fatale ne tombe qu'au moment où l'on clique sur la fonctionnalité,
     * c'est-à-dire potentiellement devant le jury.
     */
    private function verifierDependances(): void
    {
        $lock = base_path('composer.lock');
        $installes = base_path('vendor/composer/installed.json');

        if (! is_file($lock) || ! is_file($installes)) {
            $this->controle('Dépendances PHP installées', false, 'composer install');

            return;
        }

        $declares = collect(json_decode((string) file_get_contents($lock), true)['packages'] ?? [])
            ->pluck('name');

        $contenu = json_decode((string) file_get_contents($installes), true);
        $presents = collect($contenu['packages'] ?? $contenu ?? [])->pluck('name');

        $manquants = $declares->diff($presents)->values();

        $this->controle(
            sprintf('Dépendances PHP à jour (%d/%d paquets)', $declares->count() - $manquants->count(), $declares->count()),
            $manquants->isEmpty(),
            $manquants->isEmpty()
                ? 'composer install'
                : 'composer install — '.$manquants->count().' paquet(s) manquant(s) : '
                    .$manquants->take(6)->implode(', ').($manquants->count() > 6 ? '…' : '')
        );

        if ($manquants->isNotEmpty()) {
            $this->line('     <fg=gray>Fonctionnalités concernées : import de listes (Excel/CSV),</>');
            $this->line('     <fg=gray>extraction de tableurs, PDF protégé des identifiants, OCR local.</>');
        }
    }

    private function verifierExtensions(): void
    {
        $requises = [
            'pdo_sqlite' => 'Base de données par défaut.',
            'openssl' => 'Chiffrement AES-256-GCM des données d\'identité.',
            'mbstring' => 'Normalisation des noms avant empreinte.',
            'fileinfo' => 'Import de fichiers (listes, CSV core banking).',
        ];

        foreach ($requises as $extension => $usage) {
            $this->controle(
                "Extension PHP {$extension}",
                extension_loaded($extension),
                "Activer extension={$extension} dans php.ini. {$usage}"
            );
        }

        $optionnelles = [
            'zip' => 'Extraction .docx/.xlsx des documents clients.',
            'gd' => 'Téléversement des logos dans Configuration (espace admin).',
        ];

        foreach ($optionnelles as $extension => $usage) {
            if (! extension_loaded($extension)) {
                $this->warn("  ~  Extension optionnelle {$extension} absente — {$usage}");
            }
        }
    }

    private function verifierClesApplicatives(): void
    {
        $this->controle(
            'APP_KEY définie',
            filled(config('app.key')),
            'php artisan key:generate'
        );

        $cleValide = false;
        $remede = 'echo "CLE_CIF_DEMO=$(openssl rand -base64 32)" >> .env && php artisan config:clear';

        try {
            app(GestionnaireCles::class)->cle('empreinte');
            $cleValide = true;
        } catch (Throwable $e) {
            $remede = $e->getMessage();
        }

        $this->controle('CLE_CIF_DEMO définie et valide (32 octets)', $cleValide, $remede);
    }

    private function verifierBaseDeDonnees(): void
    {
        $connectee = false;

        try {
            DB::connection()->getPdo();
            $connectee = true;
        } catch (Throwable) {
            // Rapporté comme échec ci-dessous.
        }

        $this->controle(
            'Connexion à la base de données',
            $connectee,
            'touch database/database.sqlite puis php artisan migrate:fresh --seed'
        );

        if (! $connectee) {
            return;
        }

        $migree = false;

        try {
            $migree = DB::table('migrations')->count() > 0
                && Client::query()->getConnection()->getSchemaBuilder()->hasTable('clients');
        } catch (Throwable) {
            // Rapporté comme échec ci-dessous.
        }

        $this->controle('Migrations appliquées', $migree, 'php artisan migrate:fresh --seed');
    }

    private function verifierDonneesDemonstration(): void
    {
        try {
            $clients = Client::count();
            $entrees = EntreeListe::count();
        } catch (Throwable) {
            return;
        }

        $this->controle(
            "Dossiers clients de démonstration ({$clients})",
            $clients > 0,
            'php artisan migrate:fresh --seed'
        );

        $this->controle(
            "Entrées de liste sanctions/PPE ({$entrees})",
            $entrees > 0,
            'php artisan migrate:fresh --seed'
        );

        $sansEmpreinte = 0;

        try {
            $sansEmpreinte = EntreeListe::whereNull('empreinte_nom')->count();
        } catch (Throwable) {
            // Table absente : déjà signalé plus haut.
        }

        $this->controle(
            'Toutes les entrées de liste portent une empreinte',
            $sansEmpreinte === 0,
            "{$sansEmpreinte} entrée(s) sans empreinte — php artisan listes:refiltrer --recalculer-empreintes"
        );
    }

    private function verifierModeDemonstration(): void
    {
        $this->newLine();
        $this->line('  <fg=gray>Configuration de démonstration (informatif — pas un échec d\'installation) :</>');

        // Réglage => [prêt pour la démonstration ?, ligne de .env à poser]
        $reglages = [
            'Assistant IA en simulateur local' => [
                (bool) config('assistance.forcer_simulateur', false),
                'ASSISTANCE_IA_FORCER_SIMULATEUR=true',
            ],
            'Recherche web en simulateur local' => [
                (bool) config('recherche_web.forcer_simulateur', false),
                'RECHERCHE_WEB_FORCER_SIMULATEUR=true',
            ],
            'Connectivité figée (actuel : '.config('reseau.mode_connectivite', 'auto').')' => [
                config('reseau.mode_connectivite', 'auto') !== 'auto',
                'RESEAU_MODE_CONNECTIVITE=en_ligne',
            ],
            'Traces techniques masquées à l\'écran' => [
                ! config('app.debug'),
                'APP_DEBUG=false',
            ],
            'Aucun e-mail réellement envoyé' => [
                config('mail.default') === 'log',
                'MAIL_MAILER=log',
            ],
        ];

        $aPoser = [];

        foreach ($reglages as $libelle => [$pret, $ligne]) {
            $this->line(sprintf('  <fg=%s>%s</> %s', $pret ? 'green' : 'gray', $pret ? '●' : '○', $libelle));

            if (! $pret) {
                $aPoser[] = $ligne;
            }
        }

        if ($aPoser !== []) {
            $this->newLine();
            $this->line('  <fg=yellow>Pour basculer en configuration de démonstration, poser dans .env :</>');

            foreach ($aPoser as $ligne) {
                $this->line("     <fg=yellow>{$ligne}</>");
            }

            $this->line('     <fg=yellow>puis php artisan config:clear</>');
        }
    }

    private function controle(string $libelle, bool $reussi, string $remede): void
    {
        $this->controles[] = [$libelle, $reussi, $remede];

        $this->line($reussi
            ? "  <fg=green>✔</>  {$libelle}"
            : "  <fg=red>✘</>  <fg=red>{$libelle}</>");
    }

    private function afficherSynthese(): int
    {
        $echecs = array_values(array_filter($this->controles, fn (array $c) => ! $c[1]));

        $this->newLine();

        if ($echecs === []) {
            $this->line('  <bg=green;fg=black;options=bold> INSTALLATION COMPLÈTE </> Prêt pour la démonstration.');
            $this->newLine();

            return self::SUCCESS;
        }

        $this->line('  <bg=red;fg=white;options=bold> '.count($echecs).' POINT(S) À CORRIGER </>');
        $this->newLine();

        foreach ($echecs as $echec) {
            $this->line("  <fg=red>✘</> {$echec[0]}");
            $this->line("     <fg=yellow>→ {$echec[2]}</>");
        }

        $this->newLine();

        return self::FAILURE;
    }
}
