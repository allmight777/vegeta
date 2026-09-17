<?php

namespace App\Http\Controllers\Admin\Listes;

use App\Http\Controllers\Controller;
use App\Models\EntreeListe;
use Illuminate\View\View;

class EntreeListeController extends Controller
{
    /**
     * Lecture seule, noms masqués (ce sont des personnes/entités listées, pas encore
     * confirmées comme liées à un client précis) : source, catégorie, version, date d'import.
     */
    public function index(): View
    {
        return view('admin.listes.index', [
            'entrees' => EntreeListe::orderBy('source')->orderByDesc('importee_le')->get(),
        ]);
    }
}
