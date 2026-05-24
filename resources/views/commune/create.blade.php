@extends('layouts.app')
@section('title', 'Nouvelle Commune')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">Ajouter une Commune</div>
            <div class="card-body">
                <form action="{{ route('communes.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nom de la commune *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Code (Optionnel)</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Province</label>
                            <input type="text" name="province" class="form-control" value="{{ old('province', 'Yadéga') }}" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Région</label>
                            <input type="text" name="region" class="form-control" value="{{ old('region', 'Nord') }}" readonly>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('communes.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection