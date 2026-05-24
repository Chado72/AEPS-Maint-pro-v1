<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Commune;
use App\Models\Intervention;
use App\Models\SparePart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Affiche la page de sélection des rapports
     */
    public function index()
    {
        $sites = Site::orderBy('name')->get();
        $communes = Commune::orderBy('name')->get();
        
        return view('reports.index', compact('sites', 'communes'));
    }

    /**
     * Génère le PDF selon le type demandé
     */
    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:site_fiche,intervention,monthly,commune,global',
            'site_id' => 'required_if:type,site_fiche|exists:sites,id',
            'intervention_id' => 'required_if:type,intervention|exists:interventions,id',
            'month' => 'required_if:type,monthly|date_format:Y-m',
            'commune_id' => 'required_if:type,commune|exists:communes,id',
        ]);

        $type = $request->type;
        $data = [];

        switch ($type) {
            case 'site_fiche':
                $data = $this->getSiteData($request->site_id);
                $view = 'pdf.site-fiche';
                $filename = "Fiche_Site_{$data['site']->code_site}.pdf";
                break;

            case 'intervention':
                $data = $this->getInterventionData($request->intervention_id);
                $view = 'pdf.intervention-report';
                $filename = "Rapport_Intervention_{$data['intervention']->id}.pdf";
                break;

            case 'monthly':
                $data = $this->getMonthlyData($request->month);
                $view = 'pdf.monthly-synthesis';
                $filename = "Synthese_Mensuelle_{$request->month}.pdf";
                break;

            case 'commune':
                $data = $this->getCommuneData($request->commune_id);
                $view = 'pdf.commune-report'; // À créer si besoin spécifique
                $filename = "Bilan_Commune_{$data['commune']->name}.pdf";
                break;

            case 'global':
                $data = $this->getGlobalData();
                $view = 'pdf.global-report';
                $filename = "Rapport_Global_Province_" . date('Y') . ".pdf";
                break;
            
            default:
                abort(404, 'Type de rapport inconnu');
        }

        $pdf = Pdf::loadView($view, $data);
        
        // Options PDF (A4, orientation)
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download($filename);
    }

    // --- Méthodes privées pour préparer les données ---

    private function getSiteData($siteId)
    {
        $site = Site::with(['village.commune', 'boreholes', 'energySources', 'interventions.user'])
                    ->findOrFail($siteId);
        
        // Calculs rapides
        $totalInterventions = $site->interventions->count();
        $coutTotal = $site->interventions->sum('cost_total');
        $dernierePanne = $site->interventions()->where('type_intervention', 'CURATIF')->latest()->first();

        return compact('site', 'totalInterventions', 'coutTotal', 'dernierePanne');
    }

    private function getInterventionData($interventionId)
    {
        $intervention = Intervention::with(['site', 'borehole', 'user', 'pieces.sparePart'])
                                    ->findOrFail($interventionId);
        
        return ['intervention' => $intervention];
    }

    private function getMonthlyData($month)
    {
        // Format Y-m (ex: 2023-10)
        [$year, $monthNum] = explode('-', $month);
        
        $interventions = Intervention::with(['site.village', 'user'])
                                     ->whereYear('intervention_date', $year)
                                     ->whereMonth('intervention_date', $monthNum)
                                     ->orderBy('intervention_date')
                                     ->get();

        $stats = [
            'total' => $interventions->count(),
            'curatif' => $interventions->where('type_intervention', 'CURATIF')->count(),
            'preventif' => $interventions->where('type_intervention', 'PREVENTIF')->count(),
            'cout_total' => $interventions->sum('cost_total'),
            'sites_touches' => $interventions->pluck('site_id')->unique()->count(),
        ];

        // Top 5 des pannes récurrentes (par diagnostic simplifié)
        // Note: Une analyse plus poussée serait nécessaire pour un vrai "top panne"
        
        return compact('interventions', 'stats', 'month', 'year');
    }

    private function getCommuneData($communeId)
    {
        $commune = Commune::with(['villages.sites' => function($q) {
            $q->withCount('interventions');
        }])->findOrFail($communeId);

        $totalSites = $commune->villages->sum(function($v) { return $v->sites->count(); });
        $totalInterventions = $commune->villages->sum(function($v) { 
            return $v->sites->sum('interventions_count'); 
        });

        return compact('commune', 'totalSites', 'totalInterventions');
    }

    private function getGlobalData()
    {
        $year = date('Y');
        
        $stats = [
            'total_sites' => Site::count(),
            'sites_actifs' => Site::where('status', 'ACTIF')->count(),
            'sites_panne' => Site::where('status', 'EN_PANNE')->count(),
            'total_forages' => DB::table('boreholes')->count(),
            'interventions_annee' => Intervention::whereYear('intervention_date', $year)->count(),
            'cout_annee' => Intervention::whereYear('intervention_date', $year)->sum('cost_total'),
            'pieces_utilisees' => DB::table('intervention_pieces')->sum('quantity_used'),
        ];

        // Top 5 communes les plus actives
        $topCommunes = Commune::withCount(['villages' => function($q) {
            $q->withCount(['sites' => function($qs) {
                $qs->withCount('interventions');
            }]);
        }])
        ->get()
        ->sortByDesc(function($c) {
            return $c->villages->sum(function($v) { return $v->sites->sum('interventions_count'); });
        })
        ->take(5);

        return compact('stats', 'year', 'topCommunes');
    }
}