<style>

    /* =========================================================
       PAGE IDENTITÉ — vue consolidée d'une personne
    ========================================================= */

    .identite-page {

        --yellow: #F0E535;
        --yellow-soft: rgba(240, 229, 53, 0.12);

        --green: #30C31A;
        --green-soft: rgba(48, 195, 26, 0.10);

        --dark: #2C343D;

        --text: #1E293B;
        --muted: #64748B;
        --muted-light: #94A3B8;

        --border: #E8EDF2;
        --white: #FFFFFF;

        --danger: #DC2626;
        --danger-soft: rgba(220, 38, 38, 0.08);

        --warn: #D97706;
        --warn-soft: rgba(217, 119, 6, 0.10);

        --shadow-sm: 0 4px 15px rgba(44, 52, 61, 0.04);

        font-family: 'Plus Jakarta Sans', sans-serif;

        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* ---------- en-tête ---------- */

    .identite-header {
        background: var(--dark);
        border-radius: 18px;
        padding: 18px 20px;
        color: var(--white);
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .identite-avatar {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: rgba(240, 229, 53, 0.16);
        color: var(--yellow);
        font-size: 18px;
        flex-shrink: 0;
    }

    .identite-header h2 {
        font-size: 15px;
        font-weight: 800;
        margin: 0;
    }

    .identite-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 7px;
    }

    .chip-dark {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.86);
        background: rgba(255, 255, 255, 0.08);
        border-radius: 999px;
        padding: 3px 10px;
    }

    .chip-dark.npi { background: var(--green-soft); color: #9BE68C; }
    .chip-dark.empreinte { background: var(--warn-soft); color: #F4C67A; }

    /* ---------- blocs ---------- */

    .bloc {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .bloc-titre {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--muted-light);
    }

    .bloc-corps { padding: 14px 16px; }

    /* ---------- jauge de plafond ---------- */

    .jauge-chiffres {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .jauge-cumul {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
    }

    .jauge-plafond { font-size: 12px; color: var(--muted); }

    .jauge-bar {
        height: 9px;
        border-radius: 999px;
        background: #EEF2F7;
        margin-top: 10px;
        overflow: hidden;
    }

    .jauge-bar span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: var(--green);
        transition: width 0.4s ease;
    }

    .jauge-bar span.warn { background: var(--warn); }
    .jauge-bar span.danger { background: var(--danger); }

    .jauge-note {
        font-size: 11.5px;
        color: var(--muted);
        margin-top: 9px;
        line-height: 1.5;
    }

    /* ---------- lignes ---------- */

    .ligne {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 11px 0;
        border-bottom: 1px dashed var(--border);
    }

    .ligne:last-child { border-bottom: none; }

    .ligne-gauche {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .ligne-icone {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: var(--yellow-soft);
        color: var(--dark);
        font-size: 12px;
        flex-shrink: 0;
    }

    .ligne-titre { font-size: 13px; font-weight: 700; color: var(--text); }
    .ligne-sous  { font-size: 11.5px; color: var(--muted); margin-top: 2px; }
    .ligne-droite { font-size: 11px; color: var(--muted-light); flex-shrink: 0; }

    .ligne a { color: var(--text); text-decoration: none; }
    .ligne a:hover { text-decoration: underline; }

    /* ---------- alertes ---------- */

    .alerte {
        border-radius: 12px;
        padding: 11px 13px;
        margin-bottom: 8px;
        border: 1px solid var(--border);
        background: #FCFDFE;
    }

    .alerte:last-child { margin-bottom: 0; }
    .alerte.critique { border-color: rgba(220, 38, 38, 0.25); background: var(--danger-soft); }
    .alerte.attention { border-color: rgba(217, 119, 6, 0.25); background: var(--warn-soft); }

    .alerte-tete {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 5px;
    }

    .alerte-type { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; }
    .alerte.critique .alerte-type { color: var(--danger); }
    .alerte.attention .alerte-type { color: var(--warn); }
    .alerte-date { font-size: 10.5px; color: var(--muted-light); }
    .alerte-texte { font-size: 12.5px; color: var(--text); line-height: 1.5; }

    .vide { font-size: 12.5px; color: var(--muted-light); }

    @media (max-width: 560px) {
        .identite-header { align-items: flex-start; }
    }

</style>