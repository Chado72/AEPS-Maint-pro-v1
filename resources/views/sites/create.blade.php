@extends('layouts.app')

@section('title', 'Nouveau Site')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Ajouter un Site (AEPS / PEA)</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('sites.store') }}" method="POST">
                    @csrf
                    
                    <!-- Section 1: Localisation -->
                    <h6 class="text-primary border-bottom pb-2 mb-3">Localisation Géographique</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Commune</label>
                            <select name="commune_id" id="communeSelect" class="form-select @error('commune_id') is-invalid @enderror" required onchange="loadVillages(this.value)">
                                <option value="">Sélectionner une commune</option>
                                @foreach($communes as $commune)
                                    <option value="{{ $commune->id }}" {{ old('commune_id') == $commune->id ? 'selected' : '' }}>
                                        {{ $commune->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('commune_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Village</label>
                            <select name="village_id" id="villageSelect" class="form-select @error('village_id') is-invalid @enderror" required disabled>
                                <option value="">Sélectionnez d'abord une commune</option>
                            </select>
                            @error('village_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Section 2: Informations Générales -->
                    <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">Informations Générales</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Nom du Site</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ex: AEPS Centre Ouahigouya" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Code Site</label>
                            <input type="text" name="code_site" class="form-control @error('code_site') is-invalid @enderror" value="{{ old('code_site') }}" placeholder="Ex: YAD-001" required>
                            <small class="text-muted">Identifiant unique sur le terrain.</small>
                            @error('code_site') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label required">Type</label>
                            <select name="type_site" class="form-select @error('type_site') is-invalid @enderror" required>
                                <option value="AEPS" {{ old('type_site') == 'AEPS' ? 'selected' : '' }}>AEPS (Adduction)</option>
                                <option value="PEA" {{ old('type_site') == 'PEA' ? 'selected' : '' }}>PEA (Point d'Eau)</option>
                            </select>
                            @error('type_site') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Statut Initial</label>
                            <select name="status" class="form-select">
                                <option value="ACTIF" selected>Actif</option>
                                <option value="EN_PANNE">En Panne</option>
                                <option value="EN_CONSTRUCTION">En Construction</option>
                                <option value="ABANDONNE">Abandonné</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date Mise en Service</label>
                            <input type="date" name="date_mise_en_service" class="form-control" value="{{ old('date_mise_en_service') }}">
                        </div>
                    </div>

                    <!-- Section 3: Gestion -->
                    <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">Gestion & Contact</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom du Gérant</label>
                            <input type="text" name="manager_name" class="form-control" value="{{ old('manager_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone Gérant</label>
                            <input type="text" name="manager_phone" class="form-control" value="{{ old('manager_phone') }}" placeholder="+226 ...">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes / Observations</label>
                        <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('sites.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-success px-4">Enregistrer le Site</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Script simple pour charger les villages dynamiquement (à connecter à une route API ou controller)
function loadVillages(communeId) {
    const villageSelect = document.getElementById('villageSelect');
    villageSelect.disabled = true;
    villageSelect.innerHTML = '<option value="">Chargement...</option>';

    if (!communeId) {
        villageSelect.innerHTML = '<option value="">Sélectionnez d\'abord une commune</option>';
        return;
    }

    // Simulation : Dans un cas réel, utilisez fetch() vers une route comme /api/villages?commune_id=X
    // Ici, on rechargerait la page ou on utiliserait AJAX. 
    // Pour l'exemple statique, on affiche un message d'attente ou on suppose que les données sont pré-chargées.
    
    // Exemple de logique réelle (décommenter si route API créée) :
    /*
    fetch(`/api/villages/${communeId}`)
        .then(response => response.json())
        .then(data => {
            villageSelect.innerHTML = '<option value="">Choisir un village</option>';
            data.forEach(v => {
                villageSelect.innerHTML += `<option value="${v.id}">${v.name}</option>`;
            });
            villageSelect.disabled = false;
        });
    */
   
   // Fallback pour la démo sans AJAX : rechargement avec paramètre
   window.location.href = `{{ route('sites.create') }}?commune_id=${communeId}&old_input=true`;
}
</script>
@endsection