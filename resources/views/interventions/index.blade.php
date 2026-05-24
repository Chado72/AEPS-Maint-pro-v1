@extends('layouts.app')
@section('title', 'Historique des Interventions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tools me-2"></i>Interventions</h2>
    <a href="{{ route('interventions.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvelle Intervention</a>
</div>

<!-- Filtres -->
<form method="GET" class="card mb-3 p-2 bg-light">
    <div class="row g-2">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Rechercher un site..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select">
                <option value="">Tout type</option>
                <option value="CURATIF" {{ request('type')=='CURATIF'?'selected':'' }}>Curatif</option>
                <option value="PREVENTIF" {{ request('type')=='PREVENTIF'?'selected':'' }}>Préventif</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">Filtrer</button>
        </div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th>Date</th>
                    <th>Site</th>
                    <th>Type</th>
                    <th>Technicien</th>
                    <th>Coût</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($interventions as $int)
                <tr>
                    <td>{{ $int->intervention_date->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('sites.show', $int->site) }}" class="text-decoration-none fw-bold">{{ $int->site->name }}</a>
                        <div class="small text-muted">{{ $int->site->village->name }}</div>
                    </td>
                    <td><span class="badge {{ $int->type_intervention=='CURATIF'?'bg-danger':'bg-info' }}">{{ $int->type_intervention }}</span></td>
                    <td>{{ $int->user->name ?? 'Inconnu' }}</td>
                    <td>{{ number_format($int->cost_total, 0, ',', ' ') }} F</td>
                    <td><span class="badge bg-success">{{ $int->status }}</span></td>
                    <td>
                        <a href="{{ route('interventions.edit', $int) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-3">Aucune intervention.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $interventions->links() }}</div>
</div>
@endsection