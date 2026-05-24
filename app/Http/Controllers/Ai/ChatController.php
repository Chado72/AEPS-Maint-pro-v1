<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiSession;
use App\Models\Site;
use App\Models\Intervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Affiche l'interface de chat
     */
    public function index(Request $request)
    {
        // Récupérer la dernière session ou en créer une nouvelle
        $session = AiSession::where('user_id', Auth::id())
            ->latest('updated_at')
            ->first();
            
        $messages = $session ? json_decode($session->context_json, true)['messages'] ?? [] : [];
        
        // Contexte optionnel si on vient d'une page site
        $currentSite = null;
        if ($request->has('site_id')) {
            $currentSite = Site::find($request->site_id);
        }

        return view('ai.chat', compact('messages', 'currentSite'));
    }

    /**
     * Traite l'envoi d'un message à l'IA
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'context_site_id' => 'nullable|exists:sites,id',
        ]);

        $user = Auth::user();
        
        // Vérifier la clé API
        if (empty($user->api_key_encrypted)) {
            return back()->withErrors(['message' => 'Veuillez configurer votre clé API dans les paramètres.']);
        }

        try {
            $apiKey = Crypt::decryptString($user->api_key_encrypted);
            $provider = $user->ai_provider ?? 'mistral';
            
            // Construction du contexte technique
            $contextText = "";
            if ($request->context_site_id) {
                $site = Site::with(['boreholes', 'energySources'])->find($request->context_site_id);
                $contextText = "Contexte actuel : Site {$site->name} ({$site->type_site}). ";
                $contextText .= "Forages: {$site->boreholes->count()}. État: {$site->status}. ";
                $contextText .= "Dernières pannes connues : " . $site->interventions()->latest()->limit(3)->pluck('diagnostic')->join(', ');
            }

            // Historique récent (simplifié pour l'exemple)
            $historyMessages = [
                ["role" => "system", "content" => "Tu es un assistant technique expert pour ONEA au Burkina Faso. Tu aides à la maintenance des AEPS/PEA. Sois précis, technique et professionnel. Utilise le FCFA pour les coûts."],
                ["role" => "user", "content" => $contextText . "\nQuestion de l'utilisateur : " . $request->message]
            ];

            // Appel API selon le provider
            $response = null;
            
            if ($provider === 'mistral') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.mistral.ai/v1/chat/completions', [
                    'model' => 'mistral-tiny',
                    'messages' => $historyMessages,
                    'temperature' => 0.7,
                ]);
            } elseif ($provider === 'groq') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama3-8b-8192',
                    'messages' => $historyMessages,
                    'temperature' => 0.7,
                ]);
            }

            if ($response && $response->successful()) {
                $aiMessage = $response->json()['choices'][0]['message']['content'];
                
                // Sauvegarder la session
                $this->saveSession($request->message, $aiMessage, $request->context_site_id);

                return back()->with('ai_response', $aiMessage);
            } else {
                Log::error('Erreur IA: ' . ($response ? $response->body() : 'Pas de réponse'));
                throw new \Exception('Erreur de communication avec l\'IA.');
            }

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Erreur IA : ' . $e->getMessage()]);
        }
    }

    private function saveSession($userMsg, $aiMsg, $siteId = null)
    {
        $session = AiSession::where('user_id', Auth::id())->latest()->first();
        
        $messages = $session ? (json_decode($session->context_json, true)['messages'] ?? []) : [];
        $messages[] = ['role' => 'user', 'content' => $userMsg, 'created_at' => now()];
        $messages[] = ['role' => 'assistant', 'content' => $aiMsg, 'created_at' => now()];

        $context = [
            'title' => substr($userMsg, 0, 30) . '...',
            'site_id' => $siteId,
            'messages' => array_slice($messages, -10), // Garder les 10 derniers messages
        ];

        if ($session) {
            $session->update(['context_json' => json_encode($context)]);
        } else {
            AiSession::create([
                'user_id' => Auth::id(),
                'context_json' => json_encode($context),
            ]);
        }
    }
}