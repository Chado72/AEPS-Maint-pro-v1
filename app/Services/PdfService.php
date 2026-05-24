<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Intervention;
use App\Models\Commune;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfService
{
    /**
     * Génère la fiche technique d'un site
     */
    public function generateSiteFiche(Site $site)
    {
        // Charger les relations nécessaires
        $site->load(['village.commune', 'boreholes', 'energySources', 'interventions' => function($q) {
            $q->orderBy('intervention_date', 'desc')->limit(10);
        }]);

        $data = [
            'site' => $site,
            'generated_at' => Carbon::now()->format('d/m/Y H:i'),
            'title' => "Fiche Technique : {$site->name}",
        ];

        $html = view('pdf.site-fiche', $data)->render();
        
        return $this->renderPdf($html, "Fiche_{$site->code_site}.pdf");
    }

    /**
     * Génère le rapport détaillé d'une intervention
     */
    public function generateInterventionReport(Intervention $intervention)
    {
        $intervention->load(['site.village', 'user', 'borehole', 'pieces.sparePart']);

        $data = [
            'intervention' => $intervention,
            'generated_at' => Carbon::now()->format('d/m/Y H:i'),
            'title' => "Rapport Intervention #{$intervention->id}",
        ];

        $html = view('pdf.intervention-report', $data)->render();

        return $this->renderPdf($html, "Intervention_{$intervention->id}.pdf");
    }

    /**
     * Génère la synthèse mensuelle
     */
    public function generateMonthlyReport(string $yearMonth)
    {
        // Parsing de la date (ex: 2023-10)
        $date = Carbon::createFromFormat('Y-m', $yearMonth);
        
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        $interventions = Intervention::with(['site.village', 'user'])
            ->whereBetween('intervention_date', [$startDate, $endDate])
            ->get();

        $stats = [
            'total' => $interventions->count(),
            'curatif' => $interventions->where('type_intervention', 'CURATIF')->count(),
            'preventif' => $interventions->where('type_intervention', 'PREVENTIF')->count(),
            'cout_total' => $interventions->sum('cost_total'),
            'sites_touches' => $interventions->unique('site_id')->count(),
        ];

        $data = [
            'period' => $date->translatedFormat('F Y'), // Octobre 2023
            'startDate' => $startDate,
            'endDate' => $endDate,
            'interventions' => $interventions,
            'stats' => $stats,
            'generated_at' => Carbon::now()->format('d/m/Y H:i'),
            'title' => "Synthèse Mensuelle - {$data['period']}",
        ];

        $html = view('pdf.monthly-synthesis', $data)->render();

        return $this->renderPdf($html, "Synthese_{$yearMonth}.pdf");
    }

    /**
     * Génère le bilan par commune
     */
    public function generateCommuneReport(Commune $commune)
    {
        $commune->load(['villages.sites'interventions']);

        $sitesCount = $commune->villages->sum(function($v) {
            return $v->sites->count();
        });

        $data = [
            'commune' => $commune,
            'sitesCount' => $sitesCount,
            'generated_at' => Carbon::now()->format('d/m/Y H:i'),
            'title' => "Bilan Commune : {$commune->name}",
        ];

        $html = view('pdf.commune-report', $data)->render();

        return $this->renderPdf($html, "Bilan_{$commune->code}.pdf");
    }

    /**
     * Génère le rapport global de la province
     */
    public function generateGlobalReport()
    {
        $stats = [
            'total_sites' => Site::count(),
            'sites_actifs' => Site::where('status', 'ACTIF')->count(),
            'sites_panne' => Site::where('status', 'EN_PANNE')->count(),
            'total_forages' => \App\Models\Borehole::count(),
            'interventions_mois' => Intervention::whereMonth('intervention_date', now()->month)->count(),
            'cout_mois' => Intervention::whereMonth('intervention_date', now()->month)->sum('cost_total'),
        ];

        $data = [
            'stats' => $stats,
            'generated_at' => Carbon::now()->format('d/m/Y H:i'),
            'title' => "Rapport Général Province du Yadéga",
        ];

        $html = view('pdf.global-report', $data)->render();

        return $this->renderPdf($html, "Rapport_Global_Yadega.pdf");
    }

    /**
     * Méthode helper pour rendre le PDF et le télécharger
     */
    private function renderPdf(string $html, string $filename)
    {
        $pdf = Pdf::loadHTML($html);
        
        // Configuration de base
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download($filename);
    }
}