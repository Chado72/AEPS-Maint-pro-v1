@extends('layouts.app')
@section('title', 'Pièces de Rechange')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-boxes me-2"></i>Magasin de Pièces</h2>
    <a href="{{ route('spare-parts.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvelle Pièce</a>
</div>

@if($spareParts->where('stock_quantity', '<=', 'min_stock_alert')->count() > 0)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Attention :</strong> {{ $spareParts->where('stock_quantity', '<=', 'min_stock_alert')->count() }} pièce(s) en stock critique !
</div>
@endif

<div class="card shadow-sm">
    <table class="table table-hover mb-0">
        <thead class="bg-light">
            <tr>
                <th>Réf.</th>
                <th>Désignation</th>
                <th>Catégorie</th>
                <th>Prix Unit.</th>
                <th>Stock</th>
                <th>État</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($spareParts as $part)
            <tr>
                <td class="fw-bold text-primary">{{ $part->reference }}</td>
                <td>{{ $part->name }}</td>
                <td><span class="badge bg-secondary">{{ $part->category }}</span></td>
                <td>{{ number_format($part->unit_price, 0, ',', ' ') }}</td>
                <td class="{{ $part->stock_quantity <= $part->min_stock_alert ? 'text-danger fw-bold' : '' }}">
                    {{ $part->stock_quantity }}
                </td>
                <td>
                    @if($part->stock_quantity == 0) <span class="badge bg-danger">Rupture</span>
                    @elseif($part->stock_quantity <= $part->min_stock_alert) <span class="badge bg-warning">Bas</span>
                    @else <span class="badge bg-success">OK</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('spare-parts.edit', $part) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="card-footer bg-white">{{ $spareParts->links() }}</div>
</div>
@endsection