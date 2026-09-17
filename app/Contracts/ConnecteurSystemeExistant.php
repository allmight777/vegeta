<?php

namespace App\Contracts;

interface ConnecteurSystemeExistant
{
    /**
     * @param  string  $type  personne_physique|personne_morale
     * @param  array<string, mixed>  $criteres  npi, nom, date_naissance, numero_compte
     * @return array<string, mixed>|null champs bruts disponibles, ou null si rien trouvé
     */
    public function rechercherParCritere(string $type, array $criteres): ?array;
}
