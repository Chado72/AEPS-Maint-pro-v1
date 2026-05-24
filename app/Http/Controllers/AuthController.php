<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion
     */
    public function showLoginForm()
    {
        // Si déjà connecté, rediriger vers le dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Gère la tentative de connexion
     */
    public function login(Request $request)
    {
        // 1. Validation des données entrantes
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        // 2. Tentative de connexion
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Mise à jour de la dernière connexion
            $user->update(['last_login_at' => now()]);

            // Journalisation de l'action (Audit Log)
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'LOGIN',
                'model_type' => 'User',
                'model_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'changes_new' => ['status' => 'Connected'],
            ]);

            // Redirection selon le rôle (optionnel)
            if ($user->isAdmin()) {
                return redirect()->intended(route('dashboard'))
                    ->with('success', 'Bon retour Administrateur ' . $user->name);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Connexion réussie. Bienvenue ' . $user->name);
        }

        // Échec de la connexion
        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Journalisation de la déconnexion
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'LOGOUT',
                'model_type' => 'User',
                'model_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Vous avez été déconnecté avec succès.');
    }
}