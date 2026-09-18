<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
</head>
<body style="margin: 0; padding: 0; background-color: #F4F5F7; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.55; color: #1F2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F4F5F7; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #FFFFFF; border: 1px solid #E5E7EB;">

                    {{-- EN-TÊTE --}}
                    <tr>
                        <td style="padding: 24px 30px; border-bottom: 2px solid #B91C1C;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #B91C1C;">
                                        ⚠ Alerte PPE
                                    </td>
                                    <td align="right" style="font-size: 11px; color: #6B7280;">
                                        Notification automatique
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- TITRE --}}
                    <tr>
                        <td style="padding: 30px 30px 10px 30px;">
                            <h1 style="margin: 0; font-size: 18px; font-weight: 700; color: #111827;">
                                Correspondance PPE détectée
                            </h1>
                            <p style="margin: 6px 0 0 0; font-size: 14px; color: #4B5563;">
                                Bonjour {{ $responsableNom }}, un dossier de votre agence requiert une vérification immédiate.
                            </p>
                        </td>
                    </tr>

                    {{-- CONTEXTE --}}
                    <tr>
                        <td style="padding: 20px 30px 0 30px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #E5E7EB; font-size: 13px; color: #6B7280; width: 40%;">
                                        Dossier concerné
                                    </td>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #E5E7EB; font-size: 13px; font-weight: 700; color: #111827; text-align: right;">
                                        {{ $clientNom }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #E5E7EB; font-size: 13px; color: #6B7280;">
                                        Contexte
                                    </td>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #E5E7EB; font-size: 13px; font-weight: 700; color: #111827; text-align: right;">
                                        {{ $contexte }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- CORRESPONDANCES --}}
                    <tr>
                        <td style="padding: 24px 30px 0 30px;">
                            <p style="margin: 0 0 12px 0; font-size: 13px; font-weight: 700; color: #111827;">
                                Correspondances détectées :
                            </p>

                            @foreach ($correspondances as $c)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #FEF2F2; border-left: 3px solid #B91C1C; margin-bottom: 10px;">
                                    <tr>
                                        <td style="padding: 12px 14px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td style="font-size: 13px; font-weight: 700; color: #111827;">
                                                        {{ $c['nom_liste'] }}
                                                    </td>
                                                    <td align="right" style="font-size: 13px; font-weight: 700; color: #B91C1C;">
                                                        {{ $c['score'] }} %
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="padding-top: 4px; font-size: 11px; color: #6B7280;">
                                                        {{ $c['source'] }} — {{ $c['categorie'] }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            @endforeach
                        </td>
                    </tr>

                    {{-- ACTION --}}
                    <tr>
                        <td style="padding: 24px 30px 30px 30px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="background-color: #B91C1C;">
                                        <a href="{{ $lienFiltrage }}" style="display: inline-block; padding: 11px 22px; font-size: 13px; font-weight: 700; color: #FFFFFF; text-decoration: none;">
                                            Traiter dans Filtrage
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 14px 0 0 0; font-size: 11px; color: #6B7280; line-height: 1.6;">
                                Aucune décision n'a été prise automatiquement. Le dossier reste actif, aucune action côté client n'a été suspendue.
                            </p>
                        </td>
                    </tr>

                    {{-- PIED --}}
                    <tr>
                        <td style="padding: 18px 30px; background-color: #F9FAFB; border-top: 1px solid #E5E7EB; font-size: 11px; color: #6B7280; line-height: 1.6;">
                            Ce message vous est adressé en tant que responsable d'agence dans le cadre de la surveillance LBC/FT.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
