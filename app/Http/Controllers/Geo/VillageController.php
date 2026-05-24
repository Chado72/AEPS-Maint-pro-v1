<?php

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Models\Village;
use App\Models\Commune;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VillageController extends Controller
{
    /**
     * Affiche la liste des villages
     */
    public function index(Request $request)
    {
        $query = Village::with('commune');

        // Filtre par commune
        if ($request->filled('commune_id')) {
            $query->where('commune_id', $request->commune_id);
        }

        // Recherche par nom
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $villages = $query->orderBy('name')->paginate(15);
        $communes = Commune::orderBy('name')->get();

        return view('villages.index', compact('villages', 'communes'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        $communes = Commune::orderBy('name')->get();
        return view('villages.create', compact('communes'));
    }

    /**
     * Enregistre un nouveau village
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20|unique:villages,code',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ], [
            'commune_id.required' => 'La commune est obligatoire.',
            'commune_id.exists' => 'Cette commune n\'existe pas.',
            'name.required' => 'Le nom du village est obligatoire.',
            'code.unique' => 'Ce code de village existe déjà.',
        ]);

        $village = Village::create($validated);

        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE',
            'model_type' => 'Village',
            'model_id' => $village->id,
            'changes_new' => $validated,
        ]);

        return redirect()->route('villages.index')
            ->with('success', "Le village '{$village->name}' a été créé avec succès.");
    }

    /**
     * Affiche le détail d'un village
     */
    public function show(Village $village)
    {
        $village->load(['commune', 'sites']);
        return view('villages.show', compact('village'));
    }

    /**
     * Affiche le formulaire de modification
     */
    public function edit(Village $village)
    {
        $communes = Commune::orderBy('name')->get();
        return view('villages.edit', compact('village', 'communes'));
    }

    /**
     * Met à jour un village
     */
    public function update(Request $request, Village $village)
    {
        $validated = $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20|unique:villages,code,' . $village->id,
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $oldData = $village->toArray();
        
        $village->update($validated);

        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE',
            'model_type' => 'Village',
            'model_id' => $village->id,
            'changes_old' => $oldData,
            'changes_new' => $validated,
        ]);

        return redirect()->route('villages.index')
            ->with('success', "Le village '{$village->name}' a été mis à jour.");
    }

    /**
     * Supprime un village
     */
    public function destroy(Village $village)
    {
        // Vérifier s'il y a des sites rattachés
        if ($village->sites()->count() > 0) {
            return back()->withErrors(['error' => 'Impossible de supprimer ce village car il contient des sites AEPS/PEA.']);
        }

        $village->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE',
            'model_type' => 'Village',
            'model_id' => $village->id,
            'changes_old' => $village->toArray(),
        ]);

        return redirect()->route('villages.index')
            ->with('success', 'Le village a été supprimé.');
    }
}