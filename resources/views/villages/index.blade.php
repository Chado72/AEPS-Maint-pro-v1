@extends('layouts.app')
@section('title', 'Villages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-home me-2"></i>Villages</h2>
    <a href="{{ route('villages.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouveau Village</a>
</div>

<!-- Filtre par commune -->
<form method="GET" class="mb-3 row g-2">
    <div class="col-auto">
        <select name="commune_id" class="form-select" onchange="this.form.submit()">
            <option value="">Toutes les communes</option>
            @foreach($communes as $c)
                <option value="{{ $c->id }}" {{ request('commune_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th>Nom</th>
                    <th>Commune</th>
                    <th>Sites AEPS/PEA</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($villages as $village)
                <tr>
                    <td class="fw-bold">{{ $village->name }}</td>
                    <td>{{ $village->commune->name }}</td>
                    <td>{{ $village->sites_count ?? $village->sites->count() }}</td>
                    <td>
                        <a href="{{ route('villages.edit', $village) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-3">Aucun village trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $villages->links() }}</div>
</div>
@endsection