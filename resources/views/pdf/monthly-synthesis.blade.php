@extends('pdf.layout-pdf')

@section('title', 'Synthèse Mensuelle')

@section('content')

    <h1 class="text-center">Synthèse des Activités - {{ $monthName }} {{ $year }}</h1>
    <p class="text-center" style="margin-bottom: 20px;">Province du Yadéga - Région du Nord</p>

    <!-- 1. Statistiques Globales (Cartes) -->
    <table style="margin-bottom: 20px; text-align: center;">
        <tr>
            <th style="width: 25%;">Total Interventions</th>
            <th style="width: 25%;">Interventions Curatives</th>
            <th style="width: 25%;">Coût Total (FCFA)</th>
            <th style="width: 25%;">Sites Impactés</th>
        </tr>
        <tr>
            <td style="font-size: 16px; font-weight: bold;">{{ $stats['total'] }}</td>
            <td style="font-size: 16px; font-weight: bold; color: #dc3545;">{{ $stats['curatif'] }}</td>
            <td style="font-size: 16px; font-weight: bold;">{{ number_format($stats['cout_total'], 0, ',', ' ') }}</td>
            <td style="font-size: 16px; font-weight: bold;">{{ $stats['sites_uniques'] }}</td>
        </tr>
    </table>

    <!-- 2. Tableau Détaillé des Interventions -->
    <h2>Détail des Interventions</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Date</th>
                <th style="width: 25%;">Site / Village</th>
                <th style="width: 15%;">Type</th>
                <th style="width: 30%;">Diagnostic (Résumé)</th>
                <th style="width: 10%;" class="text-right">Coût</th>
                <th style="width: 10%;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($interventions as $int)
            <tr>
                <td>{{ \Carbon\Carbon::parse($int->intervention_date)->format('d/m/Y') }}</td>
                <td>
                    <strong>{{ $int->site->name }}</strong><br>
                    <small>{{ $int->site->village->name }}</small>
                </td>
                <td>
                    <span style="{{ $int->type_intervention == 'CURATIF' ? 'color:red; font-weight:bold;' : 'color:blue;' }}">
                        {{ $int->type_intervention }}
                    </span>
                </td>
                <td style="font-size: 10px;">{{ Str::limit($int->diagnostic, 60) }}</td>
                <td class="text-right">{{ number_format($int->cost_total, 0, ',', ' ') }}</td>
                <td>
                    @if($int->status == 'TERMINE')
                        <span class="badge bg-success">OK</span>
                    @else
                        <span class="badge bg-warning">En cours</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Aucune intervention enregistrée pour cette période.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 3. Répartition par Commune (Optionnel) -->
    @if(isset($parCommune) && count($parCommune) > 0)
        <h2 style="margin-top: 20px;">Répartition par Commune</h2>
        <table>
            <thead>
                <tr>
                    <th>Commune</th>
                    <th class="text-center">Nombre d'interventions</th>
                    <th class="text-right">Coût Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parCommune as $commune => $data)
                <tr>
                    <td>{{ $commune }}</td>
                    <td class="text-center">{{ $data['count'] }}</td>
                    <td class="text-right">{{ number_format($data['cost'], 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Signature -->
    <div class="signature-box">
        <div class="signature-cell">
            <strong>Chef de Service<br>Maintenance</strong>
        </div>
        <div class="signature-cell"></div>
        <div class="signature-cell">
            <strong>Directeur Provincial<br>ONEA Yadéga</strong>
        </div>
    </div>

@endsection