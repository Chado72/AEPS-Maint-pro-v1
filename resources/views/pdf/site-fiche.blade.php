@extends('pdf.layout-pdf')

@section('title', 'Fiche Technique - ' . $site->name)

@section('content')

    <!-- Titre du rapport -->
    <h1 style="text-align: center; font-size: 20px; margin-bottom: 20px;">
        FICHE TECHNIQUE DU SITE<br>
        <span style="font-size: 16px; color: #555;">{{ strtoupper($site->name) }}</span>
    </h1>

    <!-- 1. Informations Générales -->
    <h2>1. IDENTIFICATION</h2>
    <table>
        <tr>
            <th width="30%">Code Site</th>
            <td>{{ $site->code_site }}</td>
            <th width="30%">Type</th>
            <td><span class="badge bg-{{ $site->type_site == 'AEPS' ? 'success' : 'warning' }}">{{ $site->type_site }}</span></td>
        </tr>
        <tr>
            <th>Commune</th>
            <td>{{ $site->village->commune->name }}</td>
            <th>Village</th>
            <td>{{ $site->village->name }}</td>
        </tr>
        <tr>
            <th>Statut Actuel</th>
            <td colspan="3">
                <span class="badge bg-{{ $site->status == 'ACTIF' ? 'success' : ($site->status == 'EN_PANNE' ? 'danger' : 'secondary') }}">
                    {{ str_replace('_', ' ', $site->status) }}
                </span>
                @if($site->date_mise_en_service)
                    <span style="float:right; font-size:10px;">Mise en service : {{ $site->date_mise_en_service->format('d/m/Y') }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Gérant</th>
            <td>{{ $site->manager_name ?? 'N/A' }} ({{ $site->manager_phone ?? 'N/A' }})</td>
            <th>Coordonnées GPS</th>
            <td>{{ $site->latitude }}, {{ $site->longitude }}</td>
        </tr>
        <tr>
            <th>Notes</th>
            <td colspan="3">{{ $site->notes ?? '-' }}</td>
        </tr>
    </table>

    <!-- 2. Forages -->
    <h2>2. FORAGES ({{ $site->boreholes->count() }})</h2>
    @if($site->boreholes->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Profondeur (m)</th>
                    <th>Diamètre (mm)</th>
                    <th>Type Pompe</th>
                    <th>Statut</th>
                    <th>Installation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($site->boreholes as $forage)
                <tr>
                    <td><strong>{{ $forage->code_forage }}</strong></td>
                    <td class="text-center">{{ $forage->depth_meters ?? '-' }}</td>
                    <td class="text-center">{{ $forage->diameter_mm ?? '-' }}</td>
                    <td>{{ $forage->pump_type ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $forage->status == 'OPERATIONNEL' ? 'success' : 'danger' }}">
                            {{ $forage->status }}
                        </span>
                    </td>
                    <td class="text-center">{{ $forage->installation_date ? $forage->installation_date->format('d/m/Y') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color:#777; font-style:italic;">Aucun forage enregistré pour ce site.</p>
    @endif

    <!-- 3. Sources d'Énergie -->
    <h2>3. SOURCES D'ÉNERGIE</h2>
    @if($site->energySources->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Fournisseur</th>
                    <th>Puissance (kW)</th>
                    <th>Principale</th>
                    <th>État</th>
                    <th>Dernière Maint.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($site->energySources as $energy)
                <tr>
                    <td>{{ $energy->type_energy }}</td>
                    <td>{{ $energy->provider ?? '-' }}</td>
                    <td class="text-center">{{ $energy->capacity_kw ?? '-' }}</td>
                    <td class="text-center">{{ $energy->is_primary ? 'OUI' : 'NON' }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $energy->status == 'ACTIF' ? 'success' : 'danger' }}">
                            {{ $energy->status }}
                        </span>
                    </td>
                    <td class="text-center">{{ $energy->last_maintenance ? $energy->last_maintenance->format('d/m/Y') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color:#777; font-style:italic;">Aucune source d'énergie enregistrée.</p>
    @endif

    <!-- 4. Historique Récent des Interventions -->
    <h2>4. DERNIÈRES INTERVENTIONS (5 dernières)</h2>
    @if($site->interventions->take(5)->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Technicien</th>
                    <th>Diagnostic (Résumé)</th>
                    <th>Coût (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($site->interventions->take(5) as $int)
                <tr>
                    <td>{{ $int->intervention_date->format('d/m/Y') }}</td>
                    <td>{{ $int->type_intervention }}</td>
                    <td>{{ $int->user->name ?? 'Inconnu' }}</td>
                    <td style="font-size:10px;">{{ Str::limit($int->diagnostic, 40) }}</td>
                    <td class="text-right">{{ number_format($int->cost_total, 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <p style="font-size:10px; text-align:right; margin-top:5px;">
            Coût total des interventions affichées : <strong>{{ number_format($site->interventions->take(5)->sum('cost_total'), 0, ',', ' ') }} FCFA</strong>
        </p>
    @else
        <p style="color:#777; font-style:italic;">Aucune intervention enregistrée.</p>
    @endif

    <!-- Bloc de Signature -->
    <div class="signature-box">
        <div class="signature-cell">
            <strong>Le Technicien</strong><br><br><br>
            (Signature)
        </div>
        <div class="signature-cell">
            <strong>Le Chef de Centre</strong><br><br><br>
            (Visa)
        </div>
        <div class="signature-cell">
            <strong>La Direction Provinciale</strong><br><br><br>
            (Cachet)
        </div>
    </div>

@endsection