<style>

    [x-cloak] { display: none !important; }

    /* =========================================================
       PAGE FILTRAGE — revue des correspondances
    ========================================================= */

    .filtrage-page {

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

    /* ---------- bandeau de mesure ---------- */

    .filtrage-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }

    .stat-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 14px 16px;
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: var(--yellow-soft);
        color: var(--dark);
        font-size: 15px;
        flex-shrink: 0;
    }

    .stat-icon.green { background: var(--green-soft); color: var(--green); }
    .stat-icon.warn  { background: var(--warn-soft);  color: var(--warn); }

    .stat-value {
        font-size: 20px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.1;
    }

    .stat-label {
        font-size: 11.5px;
        color: var(--muted);
        margin-top: 2px;
    }

    /* ---------- carte de correspondance ---------- */

    .filtrage-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .match-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .match-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
    }

    .match-identity {
        display: flex;
        gap: 12px;
        min-width: 0;
    }

    .match-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: var(--dark);
        color: var(--yellow);
        flex-shrink: 0;
    }

    .match-name {
        font-size: 14.5px;
        font-weight: 700;
        color: var(--text);
    }

    .match-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 6px;
    }

    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        color: var(--muted);
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: 999px;
        padding: 3px 9px;
    }

    .meta-chip.source { color: var(--dark); background: var(--yellow-soft); border-color: transparent; }

    /* jauge de similarité */

    .score-block { text-align: right; flex-shrink: 0; }

    .score-value {
        font-size: 18px;
        font-weight: 800;
        color: var(--warn);
        line-height: 1;
    }

    .score-value.high { color: var(--danger); }

    .score-label {
        font-size: 10.5px;
        color: var(--muted-light);
        margin-top: 2px;
    }

    .score-bar {
        width: 84px;
        height: 5px;
        border-radius: 999px;
        background: #EEF2F7;
        margin-top: 7px;
        overflow: hidden;
    }

    .score-bar span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: var(--warn);
    }

    .score-bar span.high { background: var(--danger); }

    /* ---------- arbre de justification ---------- */

    .match-decision { padding: 14px 16px; }

    .decision-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--muted-light);
        margin-bottom: 8px;
    }

    .decision-select,
    .decision-textarea {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 10px 12px;
        font-family: inherit;
        font-size: 13px;
        color: var(--text);
        background: #FCFDFE;
    }

    .decision-select:focus,
    .decision-textarea:focus {
        outline: none;
        border-color: var(--dark);
        background: var(--white);
    }

    .decision-textarea { margin-top: 8px; resize: vertical; }

    .decision-hint {
        display: flex;
        align-items: flex-start;
        gap: 7px;
        font-size: 11.5px;
        color: var(--muted);
        background: #F8FAFC;
        border-radius: 10px;
        padding: 8px 10px;
        margin-top: 8px;
        line-height: 1.45;
    }

    .decision-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
        flex-wrap: wrap;
    }

    .btn-decision {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: none;
        border-radius: 12px;
        padding: 9px 14px;
        font-family: inherit;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.12s ease, filter 0.12s ease;
    }

    .btn-decision:hover { transform: translateY(-1px); filter: brightness(1.04); }

    .btn-confirmer { background: var(--danger); color: var(--white); }
    .btn-ecarter   { background: var(--dark);   color: var(--yellow); }

    /* ---------- état vide ---------- */

    .filtrage-vide {
        background: var(--white);
        border: 1px dashed var(--border);
        border-radius: 16px;
        padding: 36px 20px;
        text-align: center;
        box-shadow: var(--shadow-sm);
    }

    .filtrage-vide i { font-size: 26px; color: var(--green); }

    .filtrage-vide p {
        margin-top: 10px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text);
    }

    .filtrage-vide small {
        display: block;
        margin-top: 4px;
        font-size: 11.5px;
        color: var(--muted);
    }

    @media (max-width: 560px) {
        .match-head { flex-direction: column; }
        .score-block { text-align: left; }
    }

</style>