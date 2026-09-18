<?php

namespace App\Http\Controllers\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Models\JournalAudit;
use App\Services\Audit\Consignateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JournalAuditController extends Controller
{
    public function index(): View
    {
        return view('admin.audit.index', [
            'lignes' => JournalAudit::orderByDesc('id')->paginate(30),
            'ruptureId' => session('rupture_id'),
        ]);
    }

    public function verifierChaine(): RedirectResponse
    {
        $ruptureId = Consignateur::premiereRupture() ?? 'aucune';

        return redirect()
            ->route('admin.journal-audit.index')
            ->with('rupture_id', $ruptureId);
    }
}
