<?php

namespace App\Http\Controllers\Admin\Tableau;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Reseau;
use Illuminate\View\View;

class TableauBordController extends Controller
{
    public function index(): View
    {
        return view('admin.tableau.index', [
            'nombreReseaux' => Reseau::count(),
            'nombreAgences' => Agence::count(),
            'nombreAdmins' => Admin::count(),
            'nombreAgents' => Agent::where('actif', true)->count(),
        ]);
    }
}
