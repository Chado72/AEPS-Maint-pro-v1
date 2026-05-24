<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Intervention;
use App\Models\Commune;
use App\Models\SparePart;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Prépare les données pour une fiche de site unique.
     */
    public function getSiteData(int $siteId): array
    {
        $site = Site::with(['village.commune', 'boreholes', 'energySources', 'interventions.user'])
                    ->findOrFail($siteId);

        // Calculs statistiques pour ce site
        $totalInterventions = $site->interventions()->count();
        $coutTotal = $site->interventions()->sum('cost_total');
        $dernierePanne = $site->interventions()
                              ->where('type_intervention', 'CURATIF')
                              ->orderBy('intervention_date', 'desc')
                              ->first();

        return [
            'site' => $site,
            'stats' => [
                'total_interventions' => $totalInterventions,
                'cout_total' => $coutTotal,
                'derniere_panne' => $dernierePanne,
                'taux_disponibilite' => $this->calculateAvailabilityRate($site),
            ]
        ];
    }

    /**
     * Prépare les données pour un rapport mensuel.
     */
    public function getMonthlyData(string $yearMonth): array
    {
        $date = Carbon::createFromFormat('Y-m', $yearMonth);
        
        $interventions = Intervention::with(['site.village', 'user', 'pieces.sparePart'])
                                     ->whereYear('intervention_date', $date->year)
                                     ->whereMonth('intervention_date', $date->month)
                                     ->orderBy('intervention_date', 'desc')
                                     ->get();

        $stats = [
            'total_interventions' => $interventions->count(),
            'cout_total' => $interventions->sum('cost_total'),
            'par_type' => $interventions->groupBy('type_intervention')->map->count(),
            'par_commune' => $interventions->groupBy(function($item) {
                return $item->site->village->commune->name;
            })->map->count(),
            'top_pieces' => $this->getTopSpareParts($interventions),
        ];

        return [
            'period' => $date->translatedFormat('F Y'), // Ex: Octobre 2023
            'interventions' => $interventions,
            'stats' => $stats
        ];
    }

    /**
     * Prépare les données pour un rapport par commune.
     */
    public function getCommuneData(int $communeId, ?string $yearMonth = null): array
    {
        $commune = Commune::with('villages.sites')->findOrFail($communeId);
        
        $siteIds = $commune->villages->pluck('sites')->flatten()->pluck('id');

        $query = Intervention::whereIn('site_id', $siteIds)->with(['site', 'user']);

        if ($yearMonth) {
            $date = Carbon::createFromFormat('Y-m', $yearMonth);
            $query->whereYear('intervention_date', $date->year)
                  ->whereMonth('intervention_date', $date->month);
        }

        $interventions = $query->get();

        return [
            'commune' => $commune,
            'interventions' => $interventions,
            'stats' => [
                'total_sites' => $siteIds->count(),
                'total_interventions' => $interventions->count(),
                'cout_total' => $interventions->sum('cost_total'),
            ]
        ];
    }

    /**
     * Prépare les données pour le rapport global (Province).
     */
    public function getGlobalData(): array
    {
        $totalSites = Site::count();
        $sitesEnPanne = Site::where('status', 'EN_PANNE')->count();
        $totalForages = DB::table('boreholes')->count();
        
        $interventionsYear = Intervention::whereYear('intervention_date', now()->year)->get();

        return [
            'summary' => [
                'total_sites' => $totalSites,
                'sites_en_panne' => $sitesEnPanne,
                'total_forages' => $totalForages,
                'taux_panne' => $totalSites > 0 ? round(($sitesEnPanne / $totalSites) * 100, 2) : 0,
            ],
            'year_stats' => [
                'total_interventions' => $interventionsYear->count(),
                'cout_total_annuel' => $interventionsYear->sum('cost_total'),
                'par_type' => $interventionsYear->groupBy('type_intervention')->map->count(),
            ],
            'alertes_stock' => SparePart::whereColumn('stock_quantity', '<=', 'min_stock_alert')->get(),
        ];
    }

    /**
     * Helper: Calcule un taux de disponibilité fictif basé sur les pannes récentes.
     */
    private function calculateAvailabilityRate(Site $site): float
    {
        $lastMonth = Carbon::now()->subMonth();
        $pannes = $site->interventions()
                       ->where('type_intervention', 'CURATIF')
                       ->where('intervention_date', '>=', $lastMonth)
                       ->count();
        
        // Formule simple : 100% - (nb pannes * 5%), min 0%
        $rate = 100 - ($pannes * 5);
        return max(0, $rate);
    }

    /**
     * Helper: Top 5 des pièces les plus utilisées sur une liste d'interventions.
     */
    private function getTopSpareParts($interventions): array
    {
        $partsCount = [];
        foreach ($interventions as $intervention) {
            foreach ($intervention->pieces as $piece) {
                $name = $piece->sparePart->name ?? 'Inconnu';
                $partsCount[$name] = ($partsCount[$name] ?? 0) + $piece->quantity_used;
            }
        }
        arsort($partsCount);
        return array_slice($partsCount, 0, 5, true);
    }
}