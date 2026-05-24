<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AiService
{
    /**
     * Envoie une requête à l'IA configurée pour l'utilisateur
     * 
     * @param User $user L'utilisateur qui pose la question
     * @param string $message Le message de l'utilisateur
     * @param array $context Données contextuelles (ex: infos du site)
     * @return string La réponse de l'IA
     */
    public function chat(User $user, string $message, array $context = []): string
    {
        $provider = $user->ai_provider ?? 'mistral';
        $apiKey = $user->api_key_encrypted;

        if (empty($apiKey)) {
            return "Erreur : Aucune clé API configurée pour le provider {$provider}. Veuillez aller dans les paramètres.";
        }

        // Construction du prompt système avec le contexte métier ONEA
        $systemPrompt = "Tu es un assistant technique expert pour l'ONEA (Burkina Faso), spécialisé dans la maintenance des AEPS et PEA dans la province du Yadéga. 
        Ton rôle est d'aider les techniciens à diagnostiquer des pannes, suggérer des pièces de rechange et rédiger des rapports.
        Sois concis, professionnel et technique. Utilise le français.
        Contexte fourni : " . json_encode($context);

        try {
            if ($provider === 'mistral') {
                return $this->callMistral($apiKey, $systemPrompt, $message);
            } elseif ($provider === 'groq') {
                return $this->callGroq($apiKey, $systemPrompt, $message);
            } else {
                return "Provider inconnu : {$provider}";
            }
        } catch (Exception $e) {
            Log::error("Erreur IA : " . $e->getMessage());
            return "Désolé, une erreur est survenue lors de la connexion à l'IA (" . $e->getMessage() . "). Vérifiez votre clé API ou votre connexion internet.";
        }
    }

    /**
     * Appel API Mistral
     */
    private function callMistral(string $apiKey, string $systemPrompt, string $userMessage): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.mistral.ai/v1/chat/completions', [
            'model' => 'mistral-tiny', // Ou mistral-small, mistral-medium
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.7,
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'];
        }

        throw new Exception("Mistral API Error: " . $response->status());
    }

    /**
     * Appel API Groq (Très rapide)
     */
    private function callGroq(string $apiKey, string $systemPrompt, string $userMessage): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama3-8b-8192', // Modèle Llama 3 sur Groq
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.7,
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'];
        }

        throw new Exception("Groq API Error: " . $response->status());
    }
}