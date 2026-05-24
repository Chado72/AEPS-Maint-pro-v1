<?php

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use Illuminate\Http\Request;

class CommuneController extends Controller
{
    /**
     * Affiche la liste des communes
     */
    public function index()
    {
        $communes = Commune::orderBy('name', 'asc')->paginate(10);
        return view('communes.index', compact('communes'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        return view('communes.create');
    }

    /**
     * Enregistre une nouvelle commune
     */
    public function store(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:communes,code',
            'name' => 'required|string|max:100',
            'province' => 'nullable|string|max:50',
            'region' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        Commune::create($validated);

        return redirect()->route('communes.index')
                         ->with('success', 'Commune créée avec succès.');
    }

    /**
     * Affiche les détails d'une commune (optionnel, souvent on va direct aux villages)
     */
    public function show(Commune $commune)
    {
        // Charge les villages liés
        $commune->load('villages');
        return view('communes.show', compact('commune'));
    }

    /**
     * Affiche le formulaire de modification
     */
    public function edit(Commune $commune)
    {
        return view('communes.edit', compact('commune'));
    }

    /**
     * Met à jour la commune
     */
    public function update(Request $request, Commune $commune)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:communes,code,' . $commune->id,
            'name' => 'required|string|max:100',
            'province' => 'nullable|string|max:50',
            'region' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $commune->update($validated);

        return redirect()->route('communes.index')
                         ->with('success', 'Commune mise à jour avec succès.');
    }

    /**
     * Supprime la commune
     */
    public function destroy(Commune $commune)
    {
        // Vérification : ne pas supprimer si des villages sont liés
        if ($commune->villages()->count() > 0) {
            return redirect()->route('communes.index')
                             ->with('error', 'Impossible de supprimer : cette commune contient des villages.');
        }

        $commune->delete();

        return redirect()->route('communes.index')
                         ->with('success', 'Commune supprimée avec succès.');
    }
}