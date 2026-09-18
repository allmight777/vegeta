<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alerte conformité</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F4F5F7; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.55; color: #1F2937;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F4F5F7; padding: 40px 20px;">
        <tr>
            <td align="center">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #FFFFFF; border: 1px solid #E5E7EB;">

                    {{-- EN-TÊTE INSTITUTIONNEL --}}
                    <tr>
                        <td style="padding: 24px 30px; border-bottom: 2px solid #2C343D;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #2C343D;">
                                        CIF-Empreinte
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
                                Alerte de conformité
                            </h1>
                            <p style="margin: 6px 0 0 0; font-size: 14px; color: #4B5563;">
                                {{ $typeLibelle }}
                            </p>
                        </td>
                    </tr>

                    {{-- TABLEAU SYNTHÈSE --}}
                    <tr>
                        <td style="padding: 20px 30px 0 30px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #E5E7EB; font-size: 13px; color: #6B7280; width: 40%;">
                                        Gravité
                                    </td>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #E5E7EB; font-size: 13px; font-weight: 700; color: {{ $gravite === 'critique' ? '#B91C1C' : '#B45309' }}; text-align: right;">
                                        {{ ucfirst($gravite) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #E5E7EB; font-size: 13px; color: #6B7280;">
                                        Agence concernée
                                    </td>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #E5E7EB; font-size: 13px; font-weight: 700; color: #111827; text-align: right;">
                                        {{ $agenceNom }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- EXPLICATION --}}
                    <tr>
                        <td style="padding: 20px 30px 0 30px;">
                            <p style="margin: 0; font-size: 14px; color: #1F2937; line-height: 1.65;">
                                {{ $explication }}
                            </p>
                        </td>
                    </tr>

                    {{-- ACTION --}}
                    <tr>
                        <td style="padding: 24px 30px 30px 30px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="background-color: #2C343D;">
                                        <a href="{{ $lienDashboard }}" style="display: inline-block; padding: 11px 22px; font-size: 13px; font-weight: 700; color: #FFFFFF; text-decoration: none;">
                                            Consulter le tableau de bord
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- PIED DE PAGE --}}
                    <tr>
                        <td style="padding: 18px 30px; background-color: #F9FAFB; border-top: 1px solid #E5E7EB; font-size: 11px; color: #6B7280; line-height: 1.6;">
                            Ce message vous est adressé en tant que responsable d'agence dans le cadre de la surveillance LBC/FT. Merci de ne pas y répondre directement.
                        </td>
                    </tr>

                </table>

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; margin-top: 16px;">
                    <tr>
                        <td align="center" style="font-size: 11px; color: #9CA3AF;">
                            CIF-Empreinte — Plateforme de gestion des accès
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
