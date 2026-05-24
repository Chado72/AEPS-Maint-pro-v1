@extends('layouts.app')

@section('title', 'Communes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-map-marked-alt me-2"></i>Communes de la Province</h2>
    <a href="{{ route('communes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouvelle Commune
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Région</th>
                    <th>Villages rattachés</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($communes as $commune)
                <tr>
                    <td>{{ $commune->code ?? '-' }}</td>
                    <td class="fw-bold">{{ $commune->name }}</td>
                    <td><span class="badge bg-info">{{ $commune->region }}</span></td>
                    <td>{{ $commune->villages_count ?? $commune->villages->count() }}</td>
                    <td>
                        <a href="{{ route('communes.edit', $commune) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('communes.destroy', $commune) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-3">Aucune commune trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $communes->links() }}</div>
</div>
@endsection