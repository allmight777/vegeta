<?php

namespace App\Http\Controllers\Admin\Detection;

use App\Http\Controllers\Controller;
use App\Models\RegleDetection;
use Illuminate\View\View;

class RegleDetectionController extends Controller
{
    /**
     * Lecture seule : les 4 règles du MVP, chacune avec sa source (§10 « chaque paramètre
     * réglementaire affiche sa source dans l'interface admin »).
     */
    public function index(): View
    {
        return view('admin.detection.index', ['regles' => RegleDetection::orderBy('code')->get()]);
    }
}
