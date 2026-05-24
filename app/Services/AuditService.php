<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuditService
{
    /**
     * Enregistre une action dans le journal d'audit.
     *
     * @param string $action Type d'action (CREATE, UPDATE, DELETE, LOGIN, EXPORT_PDF, etc.)
     * @param mixed $model L'objet modèle concerné (ex: un Site, une Intervention)
     * @param array|null $changesOld État avant modification (tableau associatif)
     * @param array|null $changesNew État après modification (tableau associatif)
     * @param string|null $description Description optionnelle de l'action
     * @return AuditLog L'enregistrement créé
     */
    public function log(
        string $action, 
        $model, 
        ?array $changesOld = null, 
        ?array $changesNew = null, 
        ?string $description = null
    ): AuditLog {
        
        // Récupération de la requête HTTP actuelle pour l'IP et l'User-Agent
        $request = request();

        return AuditLog::create([
            'user_id'       => Auth::id(), // Null si action système ou utilisateur non connecté
            'action'        => $action,
            'model_type'    => is_object($model) ? get_class($model) : $model,
            'model_id'      => is_object($model) ? $model->getKey() : null,
            'ip_address'    => $request ? $request->ip() : '127.0.0.1',
            'user_agent'    => $request ? $request->userAgent() : 'CLI/System',
            'changes_old'   => $changesOld,
            'changes_new'   => $changesNew,
            // Vous pouvez ajouter un champ 'description' si vous modifiez la migration/table
        ]);
    }

    /**
     * Helper pour logger une connexion réussie.
     */
    public function logLogin(): void
    {
        $this->log('LOGIN', 'User', null, ['status' => 'Connected'], 'Connexion utilisateur');
    }

    /**
     * Helper pour logger une déconnexion.
     */
    public function logLogout(): void
    {
        $this->log('LOGOUT', 'User', null, ['status' => 'Disconnected'], 'Déconnexion utilisateur');
    }

    /**
     * Helper pour logger la génération d'un rapport PDF.
     */
    public function logPdfExport(string $reportType, $model = null): void
    {
        $this->log('EXPORT_PDF', $model ?? 'Report', null, ['type' => $reportType], "Génération du rapport {$reportType}");
    }
}