<?php

namespace App\Contracts;

interface MoteurRechercheWeb
{
    /**
     * @return array<int, array{titre: string, url: string, extrait: string}>
     */
    public function rechercher(string $requete): array;
}
