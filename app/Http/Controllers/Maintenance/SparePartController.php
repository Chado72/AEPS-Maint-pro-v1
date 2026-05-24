<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\SparePart;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SparePartController extends Controller
{
    /**
     * Affiche la liste des pièces avec alertes de stock
     */
    public function index(Request $request)
    {
        $query = SparePart::query();

        // Filtres
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('reference', 'like', "%{$request->search}%");
            });
        }

        // Tri par état de stock en premier (rupture puis bas)
        $spareParts = $query->orderByRaw('CASE WHEN stock_quantity = 0 THEN 1 WHEN stock_quantity <= min_stock_alert THEN 2 ELSE 3 END')
                            ->orderBy('name')
                            ->paginate(15);

        return view('spare-parts.index', compact('spareParts'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        return view('spare-parts.create');
    }

    /**
     * Enregistre une nouvelle pièce
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|unique:spare_parts,reference|max:50',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_alert' => 'required|integer|min:1',
            'supplier_info' => 'nullable|string',
        ], [
            'reference.unique' => 'Cette référence existe déjà.',
            'stock_quantity.min' => 'Le stock ne peut pas être négatif.',
        ]);

        $piece = SparePart::create($validated);

        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE',
            'model_type' => 'SparePart',
            'model_id' => $piece->id,
            'changes_new' => $validated,
        ]);

        return redirect()->route('spare-parts.index')
            ->with('success', "La pièce '{$piece->name}' a été ajoutée au magasin.");
    }

    /**
     * Affiche le détail d'une pièce (optionnel, souvent intégré à l'index ou edit)
     */
    public function show(SparePart $sparePart)
    {
        // Redirection vers edit ou vue dédiée si nécessaire
        return redirect()->route('spare-parts.edit', $sparePart);
    }

    /**
     * Affiche le formulaire de modification
     */
    public function edit(SparePart $sparePart)
    {
        return view('spare-parts.edit', compact('sparePart'));
    }

    /**
     * Met à jour une pièce
     */
    public function update(Request $request, SparePart $sparePart)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50|unique:spare_parts,reference,' . $sparePart->id,
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_alert' => 'required|integer|min:1',
            'supplier_info' => 'nullable|string',
        ]);

        $oldData = $sparePart->toArray();
        
        $sparePart->update($validated);

        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE',
            'model_type' => 'SparePart',
            'model_id' => $sparePart->id,
            'changes_old' => $oldData,
            'changes_new' => $validated,
        ]);

        return redirect()->route('spare-parts.index')
            ->with('success', "La pièce '{$sparePart->name}' a été mise à jour.");
    }

    /**
     * Supprime une pièce (Soft Delete ou Hard Delete selon config)
     */
    public function destroy(SparePart $sparePart)
    {
        // Vérifier si la pièce est utilisée dans des interventions récentes (optionnel)
        // if ($sparePart->interventionPieces()->count() > 0) { ... }

        $sparePart->delete(); // Soft delete si le trait est utilisé

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE',
            'model_type' => 'SparePart',
            'model_id' => $sparePart->id,
            'changes_old' => $sparePart->toArray(),
        ]);

        return redirect()->route('spare-parts.index')
            ->with('success', 'La pièce a été supprimée.');
    }
    
    /**
     * Ajustement rapide de stock (Fonction utilitaire)
     */
    public function adjustStock(Request $request, SparePart $sparePart)
    {
        $request->validate([
            'quantity' => 'required|integer', // Positif pour ajout, négatif pour retrait
            'reason' => 'required|string'
        ]);

        $newStock = $sparePart->stock_quantity + $request->quantity;
        
        if ($newStock < 0) {
            return back()->withErrors(['quantity' => 'Le stock ne peut pas être négatif.']);
        }

        $oldStock = $sparePart->stock_quantity;
        $sparePart->update(['stock_quantity' => $newStock]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'ADJUST_STOCK',
            'model_type' => 'SparePart',
            'model_id' => $sparePart->id,
            'changes_old' => ['stock' => $oldStock],
            'changes_new' => ['stock' => $newStock, 'reason' => $request->reason],
        ]);

        return back()->with('success', 'Stock mis à jour avec succès.');
    }
}