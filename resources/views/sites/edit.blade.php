@extends('layouts.app')

@section('title', 'Modifier le Site')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Modification du Site : {{ $site->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('sites.update', $site) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Section 1: Localisation -->
                    <h6 class="text-primary border-bottom pb-2 mb-3">Localisation</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Commune</label>
                            <select name="commune_id" id="communeSelect" class="form-select @error('commune_id') is-invalid @enderror" required onchange="loadVillages(this.value)">
                                <option value="">Sélectionner...</option>
                                @foreach($communes as $commune)
                                    <option value="{{ $commune->id }}" {{ old('commune_id', $site->village->commune_id) == $commune->id ? 'selected' : '' }}>
                                        {{ $commune->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('commune_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Village</label>
                            <select name="village_id" id="villageSelect" class="form-select @error('village_id') is-invalid @enderror" required>
                                <!-- Rempli par JS -->
                                <option value="{{ $site->village_id }}" selected>{{ $site->village->name }}</option>
                            </select>
                            @error('village_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Section 2: Informations Générales -->
                    <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">Informations Techniques</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom du Site</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $site->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Code Site (Unique)</label>
                            <input type="text" name="code_site" class="form-control @error('code_site') is-invalid @enderror" value="{{ old('code_site', $site->code_site) }}" required>
                            @error('code_site') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select name="type_site" class="form-select @error('type_site') is-invalid @enderror" required>
                                <option value="AEPS" {{ old('type_site', $site->type_site) == 'AEPS' ? 'selected' : '' }}>AEPS</option>
                                <option value="PEA" {{ old('type_site', $site->type_site) == 'PEA' ? 'selected' : '' }}>PEA</option>
                            </select>
                            @error('type_site') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="ACTIF" {{ old('status', $site->status) == 'ACTIF' ? 'selected' : '' }}>Actif</option>
                                <option value="EN_PANNE" {{ old('status', $site->status) == 'EN_PANNE' ? 'selected' : '' }}>En Panne</option>
                                <option value="ABANDONNE" {{ old('status', $site->status) == 'ABANDONNE' ? 'selected' : '' }}>Abandonné</option>
                                <option value="EN_CONSTRUCTION" {{ old('status', $site->status) == 'EN_CONSTRUCTION' ? 'selected' : '' }}>En Construction</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mise en service</label>
                            <input type="date" name="date_mise_en_service" class="form-control" value="{{ old('date_mise_en_service', $site->date_mise_en_service?->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <!-- Section 3: Gestion -->
                    <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">Gestion & Contact</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom du Gérant</label>
                            <input type="text" name="manager_name" class="form-control" value="{{ old('manager_name', $site->manager_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone Gérant</label>
                            <input type="text" name="manager_phone" class="form-control" value="{{ old('manager_phone', $site->manager_phone) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes / Observations</label>
                        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $site->notes) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('sites.show', $site) }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-warning text-dark">Mettre à jour le site</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Fonction simple pour charger les villages (à adapter avec une vraie route AJAX si nécessaire)
function loadVillages(communeId) {
    const select = document.getElementById('villageSelect');
    select.innerHTML = '<option value="">Chargement...</option>';
    
    if(!communeId) return;

    // Ici, dans un cas réel, on ferait un fetch('/api/villages?commune_id='+communeId)
    // Pour l'exemple statique, on rechargerait la page ou on utiliserait une liste pré-chargée
    // Astuce rapide : recharger la page avec le paramètre commune pour filtrer le select village dans le contrôleur
    window.location.href = "{{ route('sites.create') }}?commune_id=" + communeId; 
}
</script>
@endsection