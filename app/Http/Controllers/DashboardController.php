<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Intervention;
use App\Models\SparePart;
use App\Models\Borehole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord principal avec les indicateurs clés.
     */
    public function index()
    {
        // 1. Calcul des KPI (Indicateurs Clés de Performance)
        $totalSites = Site::count();
        $sitesEnPanne = Site::where('status', 'EN_PANNE')->count();
        $tauxDisponibilite = $totalSites > 0 ? round((($totalSites - $sitesEnPanne) / $totalSites) * 100, 1) : 0;
        
        $totalForages = Borehole::count();
        $foragesEnPanne = Borehole::where('status', 'EN_PANNE')->count();

        $interventionsCeMois = Intervention::whereMonth('intervention_date', now()->month)
                                            ->whereYear('intervention_date', now()->year)
                                            ->count();
        
        // Coût total des interventions ce mois
        $coutCeMois = Intervention::whereMonth('intervention_date', now()->month)
                                  ->whereYear('intervention_date', now()->year)
                                  ->sum('cost_total');

        // 2. Alertes Stock (Pièces en rupture ou stock bas)
        $alertesStock = SparePart::whereColumn('stock_quantity', '<=', 'min_stock_alert')
                                 ->orderBy('stock_quantity', 'asc')
                                 ->limit(5)
                                 ->get();

        // 3. Dernières Interventions (Les 5 plus récentes)
        $dernieresInterventions = Intervention::with(['site.village', 'user'])
                                              ->latest('intervention_date')
                                              ->limit(5)
                                              ->get();

        // 4. Données pour le graphique (Interventions par mois sur l'année en cours)
        $statsParMois = Intervention::selectRaw('MONTH(intervention_date) as month, COUNT(*) as count')
                                    ->whereYear('intervention_date', now()->year)
                                    ->groupBy('month')
                                    ->orderBy('month')
                                    ->pluck('count', 'month');
        
        // Remplir les mois manquants avec 0 pour avoir 12 points de données
        $dataGraphique = [];
        for($i = 1; $i <= 12; $i++) {
            $dataGraphique[] = $statsParMois[$i] ?? 0;
        }

        // 5. Répartition par type d'intervention (Curatif vs Préventif)
        $typeInterventions = Intervention::selectRaw('type_intervention, COUNT(*) as count')
                                         ->groupBy('type_intervention')
                                         ->pluck('count', 'type_intervention');
        
        $dataTypes = [
            'CURATIF' => $typeInterventions['CURATIF'] ?? 0,
            'PREVENTIF' => $typeInterventions['PREVENTIF'] ?? 0,
            'INSPECTION' => $typeInterventions['INSPECTION'] ?? 0,
        ];

        return view('dashboard.index', compact(
            'totalSites',
            'sitesEnPanne',
            'tauxDisponibilite',
            'totalForages',
            'foragesEnPanne',
            'interventionsCeMois',
            'coutCeMois',
            'alertesStock',
            'dernieresInterventions',
            'dataGraphique',
            'dataTypes'
        ));
    }
}