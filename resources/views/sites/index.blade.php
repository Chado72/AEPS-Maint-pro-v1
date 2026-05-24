@extends('layouts.app')

@section('title', 'Gestion des Sites (AEPS/PEA)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-water me-2 text-primary"></i>Sites AEPS & PEA</h2>
    <a href="{{ route('sites.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus me-1"></i> Nouveau Site
    </a>
</div>

<!-- Filtres -->
<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <form action="{{ route('sites.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Commune</label>
                <select name="commune_id" class="form-select form-select-sm">
                    <option value="">Toutes les communes</option>
                    @foreach($communes as $commune)
                        <option value="{{ $commune->id }}" {{ request('commune_id') == $commune->id ? 'selected' : '' }}>
                            {{ $commune->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="AEPS" {{ request('type') == 'AEPS' ? 'selected' : '' }}>AEPS</option>
                    <option value="PEA" {{ request('type') == 'PEA' ? 'selected' : '' }}>PEA</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Statut</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="ACTIF" {{ request('status') == 'ACTIF' ? 'selected' : '' }}>Actif</option>
                    <option value="EN_PANNE" {{ request('status') == 'EN_PANNE' ? 'selected' : '' }}>En Panne</option>
                    <option value="ABANDONNE" {{ request('status') == 'ABANDONNE' ? 'selected' : '' }}>Abandonné</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                    <i class="fas fa-filter me-1"></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tableau des sites -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="ps-4">Code</th>
                        <th>Nom du Site</th>
                        <th>Localisation</th>
                        <th>Type</th>
                        <th>Forages</th>
                        <th>Statut</th>
                        <th>Gérant</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $site)
                    <tr class="border-bottom">
                        <td class="ps-4 fw-bold text-primary">{{ $site->code_site }}</td>
                        <td>
                            <a href="{{ route('sites.show', $site) }}" class="text-decoration-none text-dark fw-semibold">
                                {{ $site->name }}
                            </a>
                        </td>
                        <td>
                            <div class="small">{{ $site->village->name }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $site->village->commune->name }}</div>
                        </td>
                        <td><span class="badge bg-info text-dark">{{ $site->type_site }}</span></td>
                        <td>
                            <span class="badge bg-secondary rounded-pill">{{ $site->boreholes->count() }}</span>
                        </td>
                        <td>
                            @include('components.status-badge', ['status' => $site->status])
                        </td>
                        <td>
                            <div class="small">{{ $site->manager_name ?? '-' }}</div>
                            @if($site->manager_phone)
                                <div class="text-muted" style="font-size: 0.75rem;"><i class="fas fa-phone-alt"></i> {{ $site->manager_phone }}</div>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('sites.show', $site) }}" class="btn btn-outline-primary" title="Voir détail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sites.edit', $site) }}" class="btn btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('reports.generate') }}?site_id={{ $site->id }}" target="_blank" class="btn btn-outline-danger" title="Fiche PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                            <p>Aucun site trouvé pour ces critères.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sites->hasPages())
    <div class="card-footer bg-white py-3">
        {{ $sites->links() }}
    </div>
    @endif
</div>
@endsection