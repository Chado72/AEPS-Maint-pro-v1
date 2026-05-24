<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Village;
use App\Models\Borehole;
use App\Models\EnergySource;
use App\Models\Intervention;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SiteController extends Controller
{
    /**
     * Affiche la liste des sites
     */
    public function index(Request $request)
    {
        $query = Site::with(['village.commune', 'boreholes', 'energySources']);

        // Filtres
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code_site', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->where('type_site', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('commune_id')) {
            $query->whereHas('village', function($q) use ($request) {
                $q->where('commune_id', $request->commune_id);
            });
        }

        $sites = $query->orderBy('name')->paginate(15);
        $communes = \App\Models\Commune::all(); // Pour le filtre

        return view('sites.index', compact('sites', 'communes'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        $villages = Village::with('commune')->get();
        return view('sites.create', compact('villages'));
    }

    /**
     * Enregistre un nouveau site
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'village_id' => 'required|exists:villages,id',
            'name' => 'required|string|max:150',
            'code_site' => 'required|string|unique:sites,code_site',
            'type_site' => 'required|in:AEPS,PEA',
            'status' => 'required|in:ACTIF,EN_PANNE,ABANDONNE,EN_CONSTRUCTION',
            'date_mise_en_service' => 'nullable|date',
            'manager_name' => 'nullable|string|max:100',
            'manager_phone' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        Site::create($validated);

        return redirect()->route('sites.index')
                         ->with('success', 'Site créé avec succès.');
    }

    /**
     * Affiche le détail d'un site (avec onglets)
     */
    public function show(Site $site)
    {
        // Chargement des relations nécessaires
        $site->load(['village.commune', 'boreholes', 'energySources', 'interventions.user', 'documents']);
        
        // Stats rapides pour le site
        $stats = [
            'total_interventions' => $site->interventions()->count(),
            'cout_total' => $site->interventions()->sum('cost_total'),
            'derniere_panle' => $site->interventions()->where('type_intervention', 'CURATIF')->latest('intervention_date')->first(),
        ];

        return view('sites.show', compact('site', 'stats'));
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Site $site)
    {
        $villages = Village::with('commune')->get();
        return view('sites.edit', compact('site', 'villages'));
    }

    /**
     * Met à jour un site
     */
    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'village_id' => 'required|exists:villages,id',
            'name' => 'required|string|max:150',
            'code_site' => 'required|string|unique:sites,code_site,' . $site->id,
            'type_site' => 'required|in:AEPS,PEA',
            'status' => 'required|in:ACTIF,EN_PANNE,ABANDONNE,EN_CONSTRUCTION',
            'date_mise_en_service' => 'nullable|date',
            'manager_name' => 'nullable|string|max:100',
            'manager_phone' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $site->update($validated);

        return redirect()->route('sites.show', $site)
                         ->with('success', 'Site mis à jour avec succès.');
    }

    /**
     * Supprime un site (Soft Delete)
     */
    public function destroy(Site $site)
    {
        // Vérifier s'il y a des interventions récentes (optionnel, selon règle métier stricte)
        // if ($site->interventions()->count() > 0) { ... }

        $site->delete();

        return redirect()->route('sites.index')
                         ->with('success', 'Site supprimé (archivé).');
    }
}