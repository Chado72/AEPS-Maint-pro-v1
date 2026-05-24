<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Models\Site;
use App\Models\Borehole;
use App\Models\SparePart;
use App\Models\InterventionPiece;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InterventionController extends Controller
{
    /**
     * Affiche la liste des interventions avec filtres
     */
    public function index(Request $request)
    {
        $query = Intervention::with(['site.village', 'user', 'borehole', 'pieces.sparePart']);

        // Filtres
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }
        if ($request->filled('type')) {
            $query->where('type_intervention', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month')) {
            $query->whereYear('intervention_date', substr($request->month, 0, 4))
                  ->whereMonth('intervention_date', substr($request->month, 5, 2));
        }

        $interventions = $query->orderBy('intervention_date', 'desc')->paginate(15);
        $sites = Site::orderBy('name')->get();

        return view('interventions.index', compact('interventions', 'sites'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        $sites = Site::with('village')->orderBy('name')->get();
        $spareParts = SparePart::where('stock_quantity', '>', 0)->orderBy('name')->get();
        
        return view('interventions.create', compact('sites', 'spareParts'));
    }

    /**
     * Enregistre une nouvelle intervention
     */
    public function store(Request $request)
    {
        // 1. Validation
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'borehole_id' => 'nullable|exists:boreholes,id',
            'intervention_date' => 'required|date',
            'type_intervention' => 'required|in:PREVENTIF,CURATIF,INSTALLATION,INSPECTION',
            'status' => 'required|in:PLANIFIE,EN_COURS,TERMINE,ANNULE',
            'diagnostic' => 'required|string|min:10',
            'actions_taken' => 'required|string|min:10',
            'cost_total' => 'nullable|numeric|min:0',
            'recommendations' => 'nullable|string',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'parts' => 'nullable|array', // Structure: [id => [spare_part_id, quantity]]
        ]);

        DB::beginTransaction();
        try {
            // 2. Création de l'intervention
            $intervention = Intervention::create([
                'site_id' => $validated['site_id'],
                'user_id' => Auth::id(),
                'borehole_id' => $validated['borehole_id'] ?? null,
                'intervention_date' => $validated['intervention_date'],
                'type_intervention' => $validated['type_intervention'],
                'status' => $validated['status'],
                'diagnostic' => $validated['diagnostic'],
                'actions_taken' => $validated['actions_taken'],
                'cost_total' => $validated['cost_total'] ?? 0,
                'recommendations' => $validated['recommendations'] ?? null,
            ]);

            // 3. Gestion des pièces utilisées
            if (!empty($validated['parts'])) {
                $totalPartsCost = 0;
                foreach ($validated['parts'] as $partData) {
                    if (empty($partData['spare_part_id']) || empty($partData['quantity'])) continue;

                    $sparePart = SparePart::find($partData['spare_part_id']);
                    if ($sparePart) {
                        // Vérification stock
                        if ($sparePart->stock_quantity < $partData['quantity']) {
                            throw new \Exception("Stock insuffisant pour la pièce : " . $sparePart->name);
                        }

                        // Création liaison
                        InterventionPiece::create([
                            'intervention_id' => $intervention->id,
                            'spare_part_id' => $sparePart->id,
                            'quantity_used' => $partData['quantity'],
                            'unit_cost_at_time' => $sparePart->unit_price,
                        ]);

                        // Décrémentation du stock
                        $sparePart->decrement('stock_quantity', $partData['quantity']);
                        
                        $totalPartsCost += ($sparePart->unit_price * $partData['quantity']);
                    }
                }
                
                // Mise à jour coût total si nécessaire (Coût main d'oeuvre + Pièces)
                // Ici on suppose que cost_total saisi inclut tout, ou on peut recalculer
                // Pour cet exemple, on garde le saisi ou on ajoute les pièces si vide
                if ($intervention->cost_total == 0 && $totalPartsCost > 0) {
                    $intervention->update(['cost_total' => $totalPartsCost]);
                }
            }

            // 4. Gestion des photos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('interventions/' . $intervention->id, 'public');
                    Document::create([
                        'file_path' => $path,
                        'file_name_original' => $photo->getClientOriginalName(),
                        'mime_type' => $photo->getMimeType(),
                        'file_size_kb' => round($photo->getSize() / 1024),
                        'documentable_type' => Intervention::class,
                        'documentable_id' => $intervention->id,
                        'uploaded_by' => Auth::id(),
                        'description' => 'Photo intervention',
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('interventions.index')->with('success', 'Intervention enregistrée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['global' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Affiche le détail d'une intervention
     */
    public function show(Intervention $intervention)
    {
        $intervention->load(['site.village.commune', 'user', 'borehole', 'pieces.sparePart', 'documents']);
        return view('interventions.show', compact('intervention'));
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Intervention $intervention)
    {
        $sites = Site::all();
        $spareParts = SparePart::all();
        $intervention->load('pieces');
        return view('interventions.edit', compact('intervention', 'sites', 'spareParts'));
    }

    /**
     * Met à jour une intervention
     */
    public function update(Request $request, Intervention $intervention)
    {
        // Logique similaire à store(), simplifiée ici pour l'exemple
        $validated = $request->validate([
            'status' => 'required|in:PLANIFIE,EN_COURS,TERMINE,ANNULE',
            'recommendations' => 'nullable|string',
            // Ajouter autres champs si nécessaire
        ]);

        $intervention->update($validated);

        return redirect()->route('interventions.show', $intervention)->with('success', 'Intervention mise à jour.');
    }

    /**
     * Supprime une intervention
     */
    public function destroy(Intervention $intervention)
    {
        // Optionnel : Réintégrer les pièces dans le stock si suppression logique requise
        
        $intervention->delete();
        return redirect()->route('interventions.index')->with('success', 'Intervention supprimée.');
    }
}