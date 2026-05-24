<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Borehole;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditService;

class BoreholeController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
        // Sécurité : seul un utilisateur connecté peut accéder
        $this->middleware('auth');
    }

    /**
     * Liste des forages d'un site spécifique
     * Route: sites/{site}/boreholes
     */
    public function index(Site $site)
    {
        $boreholes = $site->boreholes()->latest()->get();
        return view('boreholes.index', compact('site', 'boreholes'));
    }

    /**
     * Formulaire de création d'un nouveau forage
     */
    public function create(Site $site)
    {
        return view('boreholes.create', compact('site'));
    }

    /**
     * Enregistrement d'un nouveau forage
     */
    public function store(Request $request, Site $site)
    {
        // 1. Validation
        $validated = $request->validate([
            'code_forage' => 'required|string|max:50|unique:boreholes,code_forage,NULL,id,site_id,' . $site->id,
            'depth_meters' => 'nullable|numeric|min:0',
            'diameter_mm' => 'nullable|integer|min:0',
            'pump_type' => 'nullable|string|max:100',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:OPERATIONNEL,EN_PANNE,SECOURS,HS',
            'notes' => 'nullable|string',
        ], [
            'code_forage.unique' => 'Ce code de forage existe déjà pour ce site.',
            'code_forage.required' => 'Le code du forage est obligatoire.',
        ]);

        // 2. Création
        $validated['site_id'] = $site->id;
        $borehole = Borehole::create($validated);

        // 3. Audit Log
        $this->auditService->log(
            Auth::id(),
            'CREATE',
            'Borehole',
            $borehole->id,
            null,
            $validated
        );

        return redirect()->route('sites.show', $site)
            ->with('success', 'Forage "' . $borehole->code_forage . '" ajouté avec succès au site ' . $site->name . '.');
    }

    /**
     * Affichage des détails d'un forage
     */
    public function show(Site $site, Borehole $borehole)
    {
        // Vérification que le forage appartient bien au site
        if ($borehole->site_id !== $site->id) {
            abort(404, 'Forage non trouvé sur ce site.');
        }

        $interventions = $borehole->interventions()->latest()->get();

        return view('boreholes.show', compact('site', 'borehole', 'interventions'));
    }

    /**
     * Formulaire d'édition d'un forage
     */
    public function edit(Site $site, Borehole $borehole)
    {
        if ($borehole->site_id !== $site->id) {
            abort(404);
        }
        return view('boreholes.edit', compact('site', 'borehole'));
    }

    /**
     * Mise à jour d'un forage
     */
    public function update(Request $request, Site $site, Borehole $borehole)
    {
        if ($borehole->site_id !== $site->id) {
            abort(404);
        }

        // 1. Validation (ignore unique pour l'ID actuel)
        $validated = $request->validate([
            'code_forage' => 'required|string|max:50|unique:boreholes,code_forage,' . $borehole->id . ',id,site_id,' . $site->id,
            'depth_meters' => 'nullable|numeric|min:0',
            'diameter_mm' => 'nullable|integer|min:0',
            'pump_type' => 'nullable|string|max:100',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:OPERATIONNEL,EN_PANNE,SECOURS,HS',
            'notes' => 'nullable|string',
        ]);

        // 2. Sauvegarde des anciennes valeurs pour l'audit
        $oldValues = $borehole->fresh()->toArray();

        // 3. Mise à jour
        $borehole->update($validated);

        // 4. Audit Log
        $this->auditService->log(
            Auth::id(),
            'UPDATE',
            'Borehole',
            $borehole->id,
            $oldValues,
            $borehole->fresh()->toArray()
        );

        return redirect()->route('sites.show', $site)
            ->with('success', 'Forage mis à jour avec succès.');
    }

    /**
     * Suppression d'un forage (Soft Delete)
     */
    public function destroy(Site $site, Borehole $borehole)
    {
        if ($borehole->site_id !== $site->id) {
            abort(404);
        }

        $code = $borehole->code_forage;
        
        // Soft delete
        $borehole->delete();

        // Audit
        $this->auditService->log(
            Auth::id(),
            'DELETE',
            'Borehole',
            $borehole->id,
            ['code_forage' => $code],
            null
        );

        return redirect()->route('sites.show', $site)
            ->with('warning', 'Le forage "' . $code . '" a été supprimé (archivé).');
    }
}