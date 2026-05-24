@extends('layouts.app')

@section('title', 'Modifier l\'Intervention')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Modification Intervention #{{ $intervention->id }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('interventions.update', $intervention) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Site</label>
                            <input type="text" class="form-control" value="{{ $intervention->site->name }}" disabled>
                            <small class="text-muted">Le site ne peut pas être changé après création.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="datetime-local" name="intervention_date" class="form-control @error('intervention_date') is-invalid @enderror" value="{{ old('intervention_date', $intervention->intervention_date->format('Y-m-d\TH:i')) }}" required>
                            @error('intervention_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select">
                                <option value="TERMINE" {{ old('status', $intervention->status) == 'TERMINE' ? 'selected' : '' }}>Terminé</option>
                                <option value="EN_COURS" {{ old('status', $intervention->status) == 'EN_COURS' ? 'selected' : '' }}>En cours</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Diagnostic</label>
                        <textarea name="diagnostic" rows="3" class="form-control @error('diagnostic') is-invalid @enderror" required>{{ old('diagnostic', $intervention->diagnostic) }}</textarea>
                        @error('diagnostic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Actions Entreprises</label>
                        <textarea name="actions_taken" rows="3" class="form-control @error('actions_taken') is-invalid @enderror" required>{{ old('actions_taken', $intervention->actions_taken) }}</textarea>
                        @error('actions_taken') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Coût Total (FCFA)</label>
                            <input type="number" step="0.01" name="cost_total" class="form-control" value="{{ old('cost_total', $intervention->cost_total) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Durée (Heures)</label>
                            <input type="number" step="0.5" name="duration_hours" class="form-control" value="{{ old('duration_hours', $intervention->duration_hours) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Recommandations</label>
                        <textarea name="recommendations" rows="2" class="form-control">{{ old('recommendations', $intervention->recommendations) }}</textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Pour modifier les pièces utilisées, veuillez supprimer cette intervention et en recréer une nouvelle, ou gérer le stock manuellement. (Simplification pour cette version).
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('interventions.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection