<?php

namespace App\Http\Controllers\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Models\JournalAudit;
use App\Services\Audit\Consignateur;
use Illuminate\View\View;

class JournalAuditController extends Controller
{
    public function index(): View
    {
        return view('admin.audit.index', [
            'lignes' => JournalAudit::orderByDesc('id')->paginate(30),
            'ruptureId' => null,
        ]);
    }

    public function verifierChaine(): View
    {
        return view('admin.audit.index', [
            'lignes' => JournalAudit::orderByDesc('id')->paginate(30),
            'ruptureId' => Consignateur::premiereRupture() ?? 'aucune',
        ]);
    }
}
