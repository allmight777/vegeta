<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
</head>
<body style="margin: 0; padding: 0; background-color: #F4F5F7; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.55; color: #1F2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F4F5F7; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0" style="max-width: 640px; width: 100%; background-color: #FFFFFF; border: 1px solid #E5E7EB;">

                    {{-- EN-TÊTE --}}
                    <tr>
                        <td style="padding: 24px 30px; border-bottom: 2px solid #2C343D;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #2C343D;">
                                        CIF-Empreinte — Récapitulatif quotidien
                                    </td>
                                    <td align="right" style="font-size: 11px; color: #6B7280;">
                                        {{ $date }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- INTRO --}}
                    <tr>
                        <td style="padding: 30px 30px 10px 30px;">
                            <h1 style="margin: 0; font-size: 18px; font-weight: 700; color: #111827;">
                                Récapitulatif PPE — {{ $agenceNom }}
                            </h1>
                            <p style="margin: 8px 0 0 0; font-size: 14px; color: #4B5563;">
                                Bonjour {{ $responsableNom }}, voici la synthèse des correspondances PPE et sanctions détectées aujourd'hui dans votre agence.
                            </p>
                        </td>
                    </tr>

                    {{-- COMPTEUR --}}
                    <tr>
                        <td style="padding: 20px 30px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #FEF2F2; border-left: 4px solid #B91C1C;">
                                <tr>
                                    <td style="padding: 16px 18px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size: 32px; font-weight: 800; color: #B91C1C; line-height: 1;">
                                                    {{ $enAttente }}
                                                </td>
                                                <td style="padding-left: 16px; font-size: 13px; color: #6B7280; vertical-align: middle;">
                                                    dossier(s) en attente de décision dans votre file de filtrage
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- CLIENTS SUSPECTS --}}
                    @if (! empty($clientsSuspects))
                        <tr>
                            <td style="padding: 10px 30px 0 30px;">
                                <p style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #111827; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Clients personnes physiques ou morales ({{ count($clientsSuspects) }})
                                </p>
                            </td>
                        </tr>
                        @foreach ($clientsSuspects as $c)
                            <tr>
                                <td style="padding: 0 30px 8px 30px;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F9FAFB; border: 1px solid #E5E7EB;">
                                        <tr>
                                            <td style="padding: 12px 14px;">
                                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td style="font-size: 13px; font-weight: 700; color: #111827;">
                                                            {{ $c['nom'] }}
                                                        </td>
                                                        <td align="right" style="font-size: 13px; font-weight: 700; color: #B91C1C;">
                                                            {{ $c['score'] }} %
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" style="padding-top: 3px; font-size: 11px; color: #6B7280;">
                                                            {{ $c['contexte'] }} — {{ $c['source'] }} — {{ $c['categorie'] }}
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- SIGNATAIRES SUSPECTS --}}
                    @if (! empty($signatairesSuspects))
                        <tr>
                            <td style="padding: 20px 30px 0 30px;">
                                <p style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #111827; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Signataires de personne morale ({{ count($signatairesSuspects) }})
                                </p>
                            </td>
                        </tr>
                        @foreach ($signatairesSuspects as $s)
                            <tr>
                                <td style="padding: 0 30px 8px 30px;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F9FAFB; border: 1px solid #E5E7EB;">
                                        <tr>
                                            <td style="padding: 12px 14px;">
                                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td style="font-size: 13px; font-weight: 700; color: #111827;">
                                                            {{ $s['nom'] }}
                                                        </td>
                                                        <td align="right" style="font-size: 13px; font-weight: 700; color: #B91C1C;">
                                                            {{ $s['score'] }} %
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" style="padding-top: 3px; font-size: 11px; color: #6B7280;">
                                                            Signataire de <strong>{{ $s['personne_morale'] }}</strong> — {{ $s['source'] }} — {{ $s['categorie'] }}
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- ACTION --}}
                    <tr>
                        <td style="padding: 24px 30px 30px 30px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="background-color: #B91C1C;">
                                        <a href="{{ $lienFiltrage }}" style="display: inline-block; padding: 12px 24px; font-size: 13px; font-weight: 700; color: #FFFFFF; text-decoration: none;">
                                            Ouvrir la file de filtrage
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- PIED --}}
                    <tr>
                        <td style="padding: 18px 30px; background-color: #F9FAFB; border-top: 1px solid #E5E7EB; font-size: 11px; color: #6B7280; line-height: 1.6;">
                            Récapitulatif automatique quotidien. Aucune décision n'est prise sans votre intervention.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
