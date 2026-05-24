@extends('pdf.layout-pdf')

@section('title', 'Rapport Annuel - Province du Yadéga')

@section('content')

    <div class="text-center" style="margin-bottom: 20px;">
        <h1 style="border:none; font-size: 20px;">RAPPORT ANNUEL DE MAINTENANCE</h1>
        <h2 style="background:none; font-size: 16px; color: #555;">Province du Yadéga - Année {{ $annee }}</h2>
        <p style="font-size: 12px; color: #777;">Généré le {{ date('d/m/Y à H:i') }}</p>
    </div>

    <!-- 1. INDICATEURS CLÉS (KPI) -->
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <th style="width: 25%; text-align: center;">Total Sites</th>
            <th style="width: 25%; text-align: center;">Interventions</th>
            <th style="width: 25%; text-align: center;">Coût Total (FCFA)</th>
            <th style="width: 25%; text-align: center;">Taux de Panne</th>
        </tr>
        <tr>
            <td class="text-center" style="font-size: 16px; font-weight: bold;">{{ $stats['total_sites'] }}</td>
            <td class="text-center" style="font-size: 16px; font-weight: bold;">{{ $stats['total_interventions'] }}</td>
            <td class="text-center" style="font-size: 16px; font-weight: bold;">{{ number_format($stats['cout_total'], 0, ',', ' ') }}</td>
            <td class="text-center" style="font-size: 16px; font-weight: bold;">{{ $stats['taux_panne'] }}%</td>
        </tr>
    </table>

    <!-- 2. RÉPARTITION PAR COMMUNE -->
    <h2>1. Activité par Commune</h2>
    <table>
        <thead>
            <tr>
                <th>Commune</th>
                <th class="text-center">Sites</th>
                <th class="text-center">Interventions</th>
                <th class="text-center">Coût (FCFA)</th>
                <th class="text-center">Principal Problème</th>
            </tr>
        </thead>
        <tbody>
            @foreach($communes_data as $commune)
            <tr>
                <td><strong>{{ $commune->nom }}</strong></td>
                <td class="text-center">{{ $commune->nb_sites }}</td>
                <td class="text-center">{{ $commune->nb_interventions }}</td>
                <td class="text-right">{{ number_format($commune->cout, 0, ',', ' ') }}</td>
                <td style="font-size: 10px;">{{ $commune->probleme_principal ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 3. ÉVOLUTION MENSUELLE -->
    <h2>2. Évolution des Interventions par Mois</h2>
    <table>
        <thead>
            <tr>
                @foreach(['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'] as $mois)
                    <th class="text-center" style="font-size: 10px;">{{ $mois }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach($stats['interventions_par_mois'] as $count)
                    <td class="text-center" style="height: 30px;">
                        <!-- Barre visuelle simple avec des caractères ou couleur de fond -->
                        @if($count > 0)
                            <div style="background-color: #0056b3; height: {{ min($count * 2, 25) }}px; margin: 0 auto; width: 80%;"></div>
                            <span style="font-size: 9px;">{{ $count }}</span>
                        @else
                            0
                        @endif
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>

    <!-- 4. TOP PIÈCES CONSOMMÉES -->
    <h2>3. Pièces de Rechange les plus utilisées</h2>
    <table>
        <thead>
            <tr>
                <th>Désignation</th>
                <th class="text-center">Quantité Totale</th>
                <th class="text-right">Coût Estimé (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($top_pieces as $piece)
            <tr>
                <td>{{ $piece->nom }}</td>
                <td class="text-center">{{ $piece->quantite }}</td>
                <td class="text-right">{{ number_format($piece->cout_total, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 5. RECOMMANDATIONS GÉNÉRALES (Simulées ou saisies) -->
    <h2>4. Recommandations de la Direction Technique</h2>
    <div style="background: #f9f9f9; padding: 10px; border-left: 4px solid #0056b3; font-style: italic;">
        <p>
            @if(!empty($recommandations))
                {!! nl2br(e($recommandations)) !!}
            @else
                L'analyse des données montre une prévalence des pannes liées aux pompes dans la commune de [Nom]. 
                Il est recommandé de renforcer le stock de pièces détachées pour le premier trimestre de l'année prochaine 
                et d'organiser une campagne de maintenance préventive dans les villages éloignés.
            @endif
        </p>
    </div>

    <!-- SIGNATURES -->
    <div class="signature-box">
        <div class="signature-cell">
            <strong>Le Chef de Service<br>Maintenance</strong>
            <br><br><br>
            (Signature)
        </div>
        <div class="signature-cell">
            <strong>Le Directeur Provincial<br>ONEA Yadéga</strong>
            <br><br><br>
            (Signature & Cachet)
        </div>
    </div>

@endsection