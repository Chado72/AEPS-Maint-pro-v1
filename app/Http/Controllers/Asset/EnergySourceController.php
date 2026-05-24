<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\EnergySource;
use App\Models\Site;
use Illuminate\Http\Request;

class EnergySourceController extends Controller
{
    /**
     * Affiche la liste des sources d'énergie (souvent via un site)
     */
    public function index()
    {
        // Par défaut, on affiche tout, mais filtrable par site
        $energySources = EnergySource::with('site')->latest()->paginate(15);
        return view('energy-sources.index', compact('energySources'));
    }

    /**
     * Formulaire de création
     */
    public function create($site_id = null)
    {
        $sites = Site::all();
        $selectedSite = $site_id ? Site::findOrFail($site_id) : null;
        
        return view('energy-sources.create', compact('sites', 'selectedSite'));
    }

    /**
     * Enregistre une nouvelle source d'énergie
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'type_energy' => 'required|in:SOLAIRE,RESEAU_ONEA,GROUPE_ELECTROGENE,HYBRIDE,AUTRE',
            'provider' => 'nullable|string|max:100',
            'capacity_kw' => 'nullable|numeric|min:0',
            'is_primary' => 'boolean',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:ACTIF,HS,MAINTENANCE',
            'notes' => 'nullable|string',
        ], [
            'site_id.required' => 'Le site est obligatoire.',
            'type_energy.in' => 'Type d\'énergie invalide.',
        ]);

        // Gestion du booléen is_primary (checkbox non cochée = false)
        $validated['is_primary'] = $request->has('is_primary');

        EnergySource::create($validated);

        return redirect()->route('sites.show', $validated['site_id'])
            ->with('success', 'Source d\'énergie ajoutée avec succès.');
    }

    /**
     * Affiche le détail d'une source
     */
    public function show(EnergySource $energySource)
    {
        return view('energy-sources.show', compact('energySource'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(EnergySource $energySource)
    {
        $sites = Site::all();
        return view('energy-sources.edit', compact('energySource', 'sites'));
    }

    /**
     * Met à jour la source d'énergie
     */
    public function update(Request $request, EnergySource $energySource)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'type_energy' => 'required|in:SOLAIRE,RESEAU_ONEA,GROUPE_ELECTROGENE,HYBRIDE,AUTRE',
            'provider' => 'nullable|string|max:100',
            'capacity_kw' => 'nullable|numeric|min:0',
            'is_primary' => 'boolean',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:ACTIF,HS,MAINTENANCE',
            'notes' => 'nullable|string',
        ]);

        $validated['is_primary'] = $request->has('is_primary');

        $energySource->update($validated);

        return redirect()->route('sites.show', $energySource->site_id)
            ->with('success', 'Source d\'énergie mise à jour.');
    }

    /**
     * Supprime la source d'énergie
     */
    public function destroy(EnergySource $energySource)
    {
        $siteId = $energySource->site_id;
        $energySource->delete(); // Soft delete si activé

        return redirect()->route('sites.show', $siteId)
            ->with('success', 'Source d\'énergie supprimée.');
    }
}