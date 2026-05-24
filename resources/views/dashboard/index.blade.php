@extends('layouts.app')

@section('title', 'Tableau de Bord')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary"><i class="fas fa-tachometer-alt me-2"></i>Tableau de Bord</h2>
        <span class="text-muted small">Dernière mise à jour : {{ now()->format('d/m/Y H:i') }}</span>
    </div>

    <!-- 1. Cartes KPI (Indicateurs Clés) -->
    <div class="row g-3 mb-4">
        <!-- Total Sites -->
        <div class="col-md-3">
            <div class="card border-start border-4 border-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Total Sites</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalSites }}</h3>
                            <small class="text-success"><i class="fas fa-check"></i> Actifs</small>
                        </div>
                        <div class="text-primary opacity-25">
                            <i class="fas fa-water fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sites en Panne -->
        <div class="col-md-3">
            <div class="card border-start border-4 border-danger shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Sites en Panne</h6>
                            <h3 class="mb-0 fw-bold text-danger">{{ $sitesEnPanne }}</h3>
                            <small class="text-danger fw-bold">Action requise !</small>
                        </div>
                        <div class="text-danger opacity-25">
                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interventions ce mois -->
        <div class="col-md-3">
            <div class="card border-start border-4 border-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Interventions (Mois)</h6>
                            <h3 class="mb-0 fw-bold">{{ $interventionsCeMois }}</h3>
                            <small class="text-muted">Coût: {{ number_format($coutCeMois, 0, ',', ' ') }} F</small>
                        </div>
                        <div class="text-warning opacity-25">
                            <i class="fas fa-tools fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertes Stock -->
        <div class="col-md-3">
            <div class="card border-start border-4 border-info shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Alertes Stock</h6>
                            <h3 class="mb-0 fw-bold">{{ $alertesStock->count() }}</h3>
                            <small class="text-info">Pièces critiques</small>
                        </div>
                        <div class="text-info opacity-25">
                            <i class="fas fa-boxes fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Section Principale : Graphique et Dernières Activités -->
    <div class="row g-4">
        
        <!-- Colonne Gauche : Graphique (Simulation visuelle simple) -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary">Évolution des Interventions ({{ date('Y') }})</h5>
                    <button class="btn btn-sm btn-outline-secondary">Voir détails</button>
                </div>
                <div class="card-body">
                    <!-- Simulation d'un graphique avec des barres CSS (ou intégrer Chart.js ici) -->
                    <div class="d-flex align-items-end justify-content-between" style="height: 200px; padding-top: 20px;">
                        @foreach($dataGraphique as $index => $count)
                            @php $height = $count > 0 ? max(10, ($count / max($dataGraphique)) * 100) : 0; @endphp
                            <div class="text-center" style="width: 6%;">
                                <div style="height: {{ $height }}%; background-color: #0056b3; width: 100%; border-radius: 4px 4px 0 0; transition: height 0.5s;"></div>
                                <small class="d-block mt-1 text-muted" style="font-size: 0.7rem;">{{ \Carbon\Carbon::create()->month($index + 1)->shortMonthName }}</small>
                                <span class="d-block fw-bold" style="font-size: 0.7rem;">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-center text-muted mt-3 small">Nombre d'interventions par mois</p>
                </div>
            </div>
        </div>

        <!-- Colonne Droite : Alertes Stock & Actions Rapides -->
        <div class="col-lg-4">
            <!-- Alertes Stock -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-bell me-2"></i>Stock Critique
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($alertesStock as $piece)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <div class="fw-bold small">{{ $piece->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">Ref: {{ $piece->reference }}</div>
                            </div>
                            <span class="badge bg-danger rounded-pill">{{ $piece->stock_quantity }} restants</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-success py-3">
                            <i class="fas fa-check-circle"></i> Aucun stock critique.
                        </li>
                    @endforelse
                </ul>
                <div class="card-footer bg-white text-center">
                    <a href="{{ route('spare-parts.index') }}" class="btn btn-sm btn-outline-danger w-100">Gérer le stock</a>
                </div>
            </div>

            <!-- Actions Rapides -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <i class="fas fa-bolt me-2"></i>Accès Rapide
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('interventions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Nouvelle Intervention
                    </a>
                    <a href="{{ route('sites.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-map-marker-alt me-2"></i>Ajouter un Site
                    </a>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-file-pdf me-2"></i>Générer un Rapport
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Tableau des Dernières Interventions -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">Dernières Interventions</h5>
            <a href="{{ route('interventions.index') }}" class="btn btn-sm btn-link">Voir tout</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Site</th>
                            <th>Type</th>
                            <th>Technicien</th>
                            <th>Diagnostic</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dernieresInterventions as $intervention)
                        <tr>
                            <td>{{ $intervention->intervention_date->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('sites.show', $intervention->site) }}" class="text-decoration-none fw-bold">
                                    {{ $intervention->site->name }}
                                </a>
                                <div class="small text-muted">{{ $intervention->site->village->name }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $intervention->type_intervention == 'CURATIF' ? 'bg-danger' : 'bg-info' }}">
                                    {{ $intervention->type_intervention }}
                                </span>
                            </td>
                            <td>{{ $intervention->user->name ?? 'Inconnu' }}</td>
                            <td class="text-truncate" style="max-width: 250px;">{{ Str::limit($intervention->diagnostic, 40) }}</td>
                            <td>
                                @include('components.status-badge', ['status' => $intervention->status])
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Aucune intervention récente.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection