@extends('layouts.app')

@section('title', $site->name)

@section('content')

{{-- En-tête de la page avec actions --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('sites.index') }}">Sites</a></li>
                <li class="breadcrumb-item"><a href="{{ route('villages.show', $site->village) }}">{{ $site->village->name }}</a></li>
                <li class="breadcrumb-item active">{{ $site->name }}</li>
            </ol>
        </nav>
        <h2 class="mb-0 d-inline-block">
            {{ $site->name }} 
            @include('components.status-badge', ['status' => $site->status])
        </h2>
        <small class="text-muted">
            <i class="fas fa-map-marker-alt me-1"></i> {{ $site->village->name }}, Commune de {{ $site->village->commune->name }}
        </small>
    </div>
    <div class="btn-group">
        <a href="{{ route('sites.edit', $site) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Modifier
        </a>
        <a href="{{ route('reports.generate', ['type' => 'site_fiche', 'site_id' => $site->id]) }}" target="_blank" class="btn btn-danger">
            <i class="fas fa-file-pdf me-1"></i> PDF
        </a>
        <form action="{{ route('sites.destroy', $site) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce site et toutes ses données associées ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    </div>
</div>

{{-- Navigation par onglets --}}
<ul class="nav nav-tabs mb-3" id="siteTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
            <i class="fas fa-info-circle me-1"></i> Informations
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="boreholes-tab" data-bs-toggle="tab" data-bs-target="#boreholes" type="button" role="tab">
            <i class="fas fa-tint me-1"></i> Forages ({{ $site->boreholes->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="energy-tab" data-bs-toggle="tab" data-bs-target="#energy" type="button" role="tab">
            <i class="fas fa-solar-panel me-1"></i> Énergie ({{ $site->energySources->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="interventions-tab" data-bs-toggle="tab" data-bs-target="#interventions" type="button" role="tab">
            <i class="fas fa-tools me-1"></i> Historique ({{ $site->interventions->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs" type="button" role="tab">
            <i class="fas fa-folder-open me-1"></i> Documents
        </button>
    </li>
</ul>

{{-- Contenu des onglets --}}
<div class="tab-content" id="siteTabsContent">

    {{-- ONGLET 1: INFORMATIONS GÉNÉRALES --}}
    <div class="tab-pane fade show active" id="info" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Code Site :</th>
                                <td>{{ $site->code_site }}</td>
                            </tr>
                            <tr>
                                <th>Type :</th>
                                <td><span class="badge bg-info">{{ $site->type_site }}</span></td>
                            </tr>
                            <tr>
                                <th>Date mise en service :</th>
                                <td>{{ $site->date_mise_en_service ? $site->date_mise_en_service->format('d/m/Y') : 'Non renseignée' }}</td>
                            </tr>
                            <tr>
                                <th>Gérant :</th>
                                <td>{{ $site->manager_name ?? '-' }} <br> <small class="text-muted">{{ $site->manager_phone ?? '' }}</small></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Commune :</th>
                                <td>{{ $site->village->commune->name }}</td>
                            </tr>
                            <tr>
                                <th>Village :</th>
                                <td>{{ $site->village->name }}</td>
                            </tr>
                            <tr>
                                <th>Coordonnées :</th>
                                <td>
                                    @if($site->latitude && $site->longitude)
                                        {{ $site->latitude }}, {{ $site->longitude }}
                                        <a href="https://www.google.com/maps?q={{ $site->latitude }},{{ $site->longitude }}" target="_blank" class="ms-1 text-primary"><i class="fas fa-map"></i></a>
                                    @else
                                        <span class="text-muted">Non définies</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Notes :</th>
                                <td>{{ $site->notes ?? 'Aucune note.' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ONGLET 2: FORAGES --}}
    <div class="tab-pane fade" id="boreholes" role="tabpanel">
        <div class="d-flex justify-content-end mb-2">
            <a href="{{ route('boreholes.create', ['site' => $site->id]) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus"></i> Ajouter un forage
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered bg-white">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Profondeur (m)</th>
                        <th>Diamètre (mm)</th>
                        <th>Pompe</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($site->boreholes as $borehole)
                    <tr>
                        <td class="fw-bold">{{ $borehole->code_forage }}</td>
                        <td>{{ $borehole->depth_meters }}</td>
                        <td>{{ $borehole->diameter_mm }}</td>
                        <td>{{ $borehole->pump_type ?? '-' }}</td>
                        <td>@include('components.status-badge', ['status' => $borehole->status])</td>
                        <td>
                            <a href="{{ route('boreholes.edit', $borehole) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">Aucun forage enregistré pour ce site.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ONGLET 3: SOURCES D'ÉNERGIE --}}
    <div class="tab-pane fade" id="energy" role="tabpanel">
        <div class="d-flex justify-content-end mb-2">
            <a href="{{ route('energy-sources.create', ['site' => $site->id]) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus"></i> Ajouter une source
            </a>
        </div>
        <div class="row">
            @forelse($site->energySources as $energy)
            <div class="col-md-4 mb-3">
                <div class="card h-100 border-start border-4 {{ $energy->is_primary ? 'border-warning' : 'border-info' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title">{{ $energy->type_energy }}</h5>
                            @if($energy->is_primary)
                                <span class="badge bg-warning text-dark">Principal</span>
                            @endif
                        </div>
                        <p class="card-text mb-1"><strong>Fournisseur:</strong> {{ $energy->provider ?? 'N/A' }}</p>
                        <p class="card-text mb-1"><strong>Puissance:</strong> {{ $energy->capacity_kw }} kW</p>
                        <p class="card-text mb-2"><strong>État:</strong> @include('components.status-badge', ['status' => $energy->status])</p>
                        <a href="{{ route('energy-sources.edit', $energy) }}" class="btn btn-sm btn-outline-secondary w-100">Modifier</a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center text-muted py-4">Aucune source d'énergie enregistrée.</div>
            @endforelse
        </div>
    </div>

    {{-- ONGLET 4: INTERVENTIONS --}}
    <div class="tab-pane fade" id="interventions" role="tabpanel">
        <div class="d-flex justify-content-end mb-2">
            <a href="{{ route('interventions.create', ['site_id' => $site->id]) }}" class="btn btn-sm btn-success">
                <i class="fas fa-plus"></i> Nouvelle Intervention
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Technicien</th>
                        <th>Diagnostic</th>
                        <th>Coût</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($site->interventions()->orderBy('intervention_date', 'desc')->limit(10)->get() as $int)
                    <tr>
                        <td>{{ $int->intervention_date->format('d/m/Y') }}</td>
                        <td><span class="badge {{ $int->type_intervention == 'CURATIF' ? 'bg-danger' : 'bg-info' }}">{{ $int->type_intervention }}</span></td>
                        <td>{{ $int->user->name ?? 'Inconnu' }}</td>
                        <td class="text-truncate" style="max-width: 200px;">{{ Str::limit($int->diagnostic, 40) }}</td>
                        <td>{{ number_format($int->cost_total, 0, ',', ' ') }} F</td>
                        <td>
                            <a href="{{ route('interventions.show', $int) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">Aucune intervention récente.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="text-end mt-2">
            <a href="{{ route('interventions.index', ['site_id' => $site->id]) }}" class="text-decoration-none">Voir tout l'historique &rarr;</a>
        </div>
    </div>

    {{-- ONGLET 5: DOCUMENTS --}}
    <div class="tab-pane fade" id="docs" role="tabpanel">
        <div class="alert alert-info py-2">
            <i class="fas fa-info-circle"></i> Gestion des documents techniques, photos et PV rattachés à ce site.
        </div>
        <!-- Liste des documents (à implémenter selon le modèle Document) -->
        <p class="text-muted text-center">Module de gestion documentaire en cours de finalisation.</p>
    </div>

</div>
@endsection