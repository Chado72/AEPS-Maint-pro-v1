@extends('pdf.layout-pdf')

@section('title', 'Rapport d\'Intervention N°' . $intervention->id)

@section('content')

    <!-- Titre du rapport -->
    <div style="text-align: center; margin-bottom: 20px;">
        <h1 style="border:none; font-size: 20px;">RAPPORT D'INTERVENTION</h1>
        <p style="font-size: 14px; color: #555;">Référence : INT-{{ $intervention->id }} / {{ $intervention->intervention_date->format('Y') }}</p>
    </div>

    <!-- 1. Informations Générales -->
    <h2>1. Informations Générales</h2>
    <table>
        <tr>
            <th width="30%">Site</th>
            <td><strong>{{ $intervention->site->name }}</strong> ({{ $intervention->site->code_site }})</td>
            <th width="20%">Village</th>
            <td>{{ $intervention->site->village->name }}</td>
        </tr>
        <tr>
            <th>Commune</th>
            <td>{{ $intervention->site->village->commune->name }}</td>
            <th>Date & Heure</th>
            <td>{{ $intervention->intervention_date->format('d/m/Y à H:i') }}</td>
        </tr>
        <tr>
            <th>Technicien</th>
            <td>{{ $intervention->user->name ?? 'Non renseigné' }}</td>
            <th>Type</th>
            <td>
                <span class="badge {{ $intervention->type_intervention == 'CURATIF' ? 'bg-danger' : 'bg-success' }}">
                    {{ $intervention->type_intervention }}
                </span>
            </td>
        </tr>
        @if($intervention->borehole)
        <tr>
            <th>Forage concerné</th>
            <td colspan="3">{{ $intervention->borehole->code_forage }} (Prof: {{ $intervention->borehole->depth_meters }}m)</td>
        </tr>
        @endif
    </table>

    <!-- 2. Diagnostic et Actions -->
    <h2>2. Détails de l'intervention</h2>
    
    <div style="margin-bottom: 15px;">
        <strong>DIAGNOSTIC / CONSTAT :</strong>
        <div style="border: 1px solid #ddd; padding: 10px; background: #fff; margin-top: 5px; min-height: 60px;">
            {!! nl2br(e($intervention->diagnostic)) !!}
        </div>
    </div>

    <div style="margin-bottom: 15px;">
        <strong>ACTIONS ENTREPRISES :</strong>
        <div style="border: 1px solid #ddd; padding: 10px; background: #fff; margin-top: 5px; min-height: 60px;">
            {!! nl2br(e($intervention->actions_taken)) !!}
        </div>
    </div>

    @if($intervention->recommendations)
    <div style="margin-bottom: 15px;">
        <strong>RECOMMANDATIONS :</strong>
        <div style="border: 1px dashed #aaa; padding: 10px; background: #f9f9f9; margin-top: 5px; font-style: italic;">
            {!! nl2br(e($intervention->recommendations)) !!}
        </div>
    </div>
    @endif

    <!-- 3. Pièces Utilisées -->
    <h2>3. Pièces de rechange utilisées</h2>
    @if($intervention->pieces->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="40%">Désignation</th>
                    <th width="15%">Référence</th>
                    <th width="10%" class="text-center">Qté</th>
                    <th width="15%" class="text-right">Prix Unit.</th>
                    <th width="15%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($intervention->pieces as $index => $piece)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $piece->sparePart->name }}</td>
                    <td>{{ $piece->sparePart->reference }}</td>
                    <td class="text-center">{{ $piece->quantity_used }}</td>
                    <td class="text-right">{{ number_format($piece->unit_cost_at_time, 0, ',', ' ') }} F</td>
                    <td class="text-right"><strong>{{ number_format($piece->total_cost, 0, ',', ' ') }} F</strong></td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="5" class="text-right"><strong>COÛT TOTAL PIÈCES :</strong></td>
                    <td class="text-right"><strong>{{ number_format($intervention->pieces->sum('total_cost'), 0, ',', ' ') }} F</strong></td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="font-style: italic; color: #777;">Aucune pièce utilisée lors de cette intervention.</p>
    @endif

    <!-- Récapitulatif Coûts -->
    <div style="margin-top: 10px; text-align: right; font-size: 14px;">
        <strong>COÛT GLOBAL DE L'INTERVENTION : {{ number_format($intervention->cost_total, 0, ',', ' ') }} FCFA</strong>
    </div>

    <!-- 4. Photos (Si disponibles) -->
    @if(isset($intervention->photos_json) && is_array($intervention->photos_json) && count($intervention->photos_json) > 0)
        <h2>4. Photos de l'intervention</h2>
        <table style="width: 100%;">
            <tr>
                @foreach($intervention->photos_json as $photo)
                <td style="width: 33%; text-align: center; padding: 5px;">
                    <!-- Note: Assurez-vous que le chemin est absolu ou accessible par DomPDF -->
                    <img src="{{ public_path('uploads/' . $photo) }}" style="max-width: 100%; height: auto; border: 1px solid #ccc;" alt="Photo intervention">
                </td>
                @endforeach
            </tr>
        </table>
    @endif

    <!-- 5. Signatures -->
    <div class="signature-box">
        <div class="signature-cell">
            <strong>Le Technicien</strong><br>
            <br><br>
            (Signature)
        </div>
        <div class="signature-cell">
            <strong>Le Chef de Centre</strong><br>
            <br><br>
            (Visa)
        </div>
        <div class="signature-cell">
            <strong>Le Responsable AEPS</strong><br>
            <br><br>
            (Visa)
        </div>
    </div>

@endsection