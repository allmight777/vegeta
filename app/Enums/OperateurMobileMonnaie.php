<?php

namespace App\Enums;

/**
 * Attribution d'opérateur par préfixe téléphonique béninois — donnée publique
 * (plan de numérotation), pas une donnée personnelle. Sert uniquement à afficher un
 * repère visuel sur l'écran récapitulatif de la simulation de dépôt (docs/DECISIONS.md).
 */
enum OperateurMobileMonnaie: string
{
    case Mtn = 'mtn';
    case Moov = 'moov';
    case Sbin = 'sbin';

    private const PREFIXES_MTN = ['42', '46', '50', '51', '52', '53', '54', '56', '57', '59', '61', '62', '66', '67', '69', '90', '91', '96', '97'];

    private const PREFIXES_MOOV = ['45', '55', '58', '60', '63', '64', '65', '68', '94', '95', '98', '99'];

    private const PREFIXES_SBIN = ['20', '21', '22', '23', '24', '28', '29', '40', '41', '43', '44', '47', '48', '49', '92', '93'];

    public static function depuisPrefixe(string $telephone): ?self
    {
        $chiffres = preg_replace('/\D/', '', $telephone) ?? '';

        // Un numéro béninois peut être saisi avec l'indicatif (229) ou le "01" national
        // récemment introduit ; on ne retient que les deux chiffres qui portent le préfixe
        // opérateur, en ignorant ces variantes.
        $chiffres = preg_replace('/^229/', '', $chiffres) ?? $chiffres;
        $chiffres = preg_replace('/^01/', '', $chiffres) ?? $chiffres;

        $prefixe = substr($chiffres, 0, 2);

        return match (true) {
            in_array($prefixe, self::PREFIXES_MTN, true) => self::Mtn,
            in_array($prefixe, self::PREFIXES_MOOV, true) => self::Moov,
            in_array($prefixe, self::PREFIXES_SBIN, true) => self::Sbin,
            default => null,
        };
    }

    public function libelle(): string
    {
        return match ($this) {
            self::Mtn => 'MTN Bénin',
            self::Moov => 'Moov Africa Bénin',
            self::Sbin => 'SBIN (Celtiis)',
        };
    }
}
