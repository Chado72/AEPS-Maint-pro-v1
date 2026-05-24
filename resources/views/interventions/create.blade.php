@extends('layouts.app')

@section('title', 'Nouvelle Intervention')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Saisie d'une Intervention</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('interventions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Site concerné</label>
                            <select name="site_id" id="siteSelect" class="form-select @error('site_id') is-invalid @enderror" required onchange="loadBoreholes(this.value)">
                                <option value="">Sélectionner un site</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                        {{ $site->name }} ({{ $site->village->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('site_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Forage spécifique (Optionnel)</label>
                            <select name="borehole_id" id="boreholeSelect" class="form-select @error('borehole_id') is-invalid @enderror">
                                <option value="">Tous les forages du site</option>
                                <!-- Rempli par JS -->
                            </select>
                            @error('borehole_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label required">Date & Heure</label>
                            <input type="datetime-local" name="intervention_date" class="form-control @error('intervention_date') is-invalid @enderror" value="{{ old('intervention_date', date('Y-m-d\TH:i')) }}" required>
                            @error('intervention_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Type</label>
                            <select name="type_intervention" class="form-select @error('type_intervention') is-invalid @enderror" required>
                                <option value="CURATIF" {{ old('type_intervention') == 'CURATIF' ? 'selected' : '' }}>Curatif (Panne)</option>
                                <option value="PREVENTIF" {{ old('type_intervention') == 'PREVENTIF' ? 'selected' : '' }}>Préventif (Entretien)</option>
                                <option value="INSPECTION" {{ old('type_intervention') == 'INSPECTION' ? 'selected' : '' }}>Inspection / Visite</option>
                            </select>
                            @error('type_intervention') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Statut initial</label>
                            <select name="status" class="form-select">
                                <option value="TERMINE" {{ old('status') == 'TERMINE' ? 'selected' : '' }}>Terminé</option>
                                <option value="EN_COURS" {{ old('status') == 'EN_COURS' ? 'selected' : '' }}>En cours</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-primary">Détails Techniques</h6>

                    <div class="mb-3">
                        <label class="form-label required">Diagnostic / Constat</label>
                        <textarea name="diagnostic" rows="3" class="form-control @error('diagnostic') is-invalid @enderror" required placeholder="Décrivez la panne ou l'état constaté...">{{ old('diagnostic') }}</textarea>
                        @error('diagnostic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Actions entreprises</label>
                        <textarea name="actions_taken" rows="3" class="form-control @error('actions_taken') is-invalid @enderror" required placeholder="Décrivez les réparations effectuées...">{{ old('actions_taken') }}</textarea>
                        @error('actions_taken') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Coût Main d'œuvre (FCFA)</label>
                            <input type="number" step="0.01" name="labor_cost" id="laborCost" class="form-control" value="{{ old('labor_cost', 0) }}" onchange="calculateTotal()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Coût Pièces (Auto-calculé)</label>
                            <input type="text" id="totalPartsCost" class="form-control" value="0" readonly>
                            <input type="hidden" name="parts_cost_total" id="partsCostHidden" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Coût Total (Estimé)</label>
                            <input type="number" step="0.01" name="cost_total" id="finalCost" class="form-control fw-bold" value="{{ old('cost_total', 0) }}" readonly>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-primary">Pièces Utilisées</h6>
                    <div id="partsContainer">
                        <!-- Lignes de pièces ajoutées dynamiquement -->
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addPartRow()">
                        <i class="fas fa-plus"></i> Ajouter une pièce
                    </button>

                    <hr class="my-4">
                    <h6 class="text-primary">Photos & Documents</h6>
                    <div class="mb-3">
                        <label class="form-label">Photos de l'intervention</label>
                        <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">Format JPG, PNG. Max 5Mo par photo.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Recommandations / Commentaires</label>
                        <textarea name="recommendations" rows="2" class="form-control">{{ old('recommendations') }}</textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="{{ route('interventions.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-success px-4">Enregistrer l'intervention</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const spareParts = @json($spareParts);

function loadBoreholes(siteId) {
    // Logique AJAX à implémenter pour charger les forages du site sélectionné
    const select = document.getElementById('boreholeSelect');
    select.innerHTML = '<option value="">Chargement...</option>';
    // Simulez ici un appel fetch ou un reload partiel
}

function addPartRow() {
    const container = document.getElementById('partsContainer');
    const rowId = Date.now();
    
    let options = '<option value="">Choisir une pièce...</option>';
    spareParts.forEach(part => {
        options += `<option value="${part.id}" data-price="${part.unit_price}">${part.name} (Stock: ${part.stock_quantity}) - ${part.unit_price} F</option>`;
    });

    const html = `
        <div class="row g-2 mb-2 part-row" id="row-${rowId}">
            <div class="col-md-5">
                <select name="parts[${rowId}][spare_part_id]" class="form-select part-select" onchange="updateRowCost(${rowId})" required>
                    ${options}
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="parts[${rowId}][quantity]" class="form-control part-qty" value="1" min="1" onchange="updateRowCost(${rowId})" required>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control row-cost" value="0" readonly>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100" onclick="removeRow(${rowId})"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function updateRowCost(rowId) {
    const row = document.getElementById(`row-${rowId}`);
    const select = row.querySelector('.part-select');
    const qty = row.querySelector('.part-qty').value;
    const price = select.options[select.selectedIndex].dataset.price || 0;
    const total = price * qty;
    
    row.querySelector('.row-cost').value = total;
    calculateTotal();
}

function removeRow(rowId) {
    document.getElementById(`row-${rowId}`).remove();
    calculateTotal();
}

function calculateTotal() {
    let partsTotal = 0;
    document.querySelectorAll('.row-cost').forEach(input => {
        partsTotal += parseFloat(input.value) || 0;
    });
    
    const labor = parseFloat(document.getElementById('laborCost').value) || 0;
    const final = partsTotal + labor;

    document.getElementById('totalPartsCost').value = partsTotal;
    document.getElementById('partsCostHidden').value = partsTotal;
    document.getElementById('finalCost').value = final; 
}
</script>
@endsection