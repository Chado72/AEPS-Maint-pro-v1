<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\AuditLog;
use App\Models\User;

class SettingController extends Controller
{
    /**
     * Affiche la page des paramètres
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Met à jour le profil utilisateur (Nom, Email, Téléphone)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];

        $user->update($validated);

        // Audit Log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'UPDATE_PROFILE',
            'model_type' => 'User',
            'model_id' => $user->id,
            'changes_old' => $oldData,
            'changes_new' => $validated,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Votre profil a été mis à jour avec succès.');
    }

    /**
     * Met à jour le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'current_password.required' => 'Veuillez entrer votre mot de passe actuel.',
            'new_password.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas.',
            'new_password.min' => 'Le mot de passe doit faire au moins 8 caractères.',
        ]);

        // Vérifier l'ancien mot de passe
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'CHANGE_PASSWORD',
            'model_type' => 'User',
            'model_id' => $user->id,
            'ip_address' => $request->ip(),
            'changes_new' => ['status' => 'Password changed'],
        ]);

        return back()->with('success', 'Votre mot de passe a été changé avec succès.');
    }

    /**
     * Met à jour la configuration IA (Provider et Clé API)
     */
    public function updateAi(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'provider' => 'required|in:mistral,groq',
            'api_key' => 'nullable|string|min:10', // On accepte null si on ne veut pas changer la clé
        ]);

        $changes = ['provider' => $validated['provider']];

        // Mise à jour du provider
        $user->ai_provider = $validated['provider'];

        // Mise à jour de la clé API seulement si une nouvelle est fournie
        if (!empty($validated['api_key'])) {
            // Ici, on devrait chiffrer la clé avant de la stocker
            // Pour l'exemple, on utilise un chiffrement simple Laravel
            $encryptedKey = encrypt($validated['api_key']);
            $user->api_key_encrypted = $encryptedKey;
            $changes['api_key'] = '***CHANGED***';
        }

        $user->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'UPDATE_AI_CONFIG',
            'model_type' => 'User',
            'model_id' => $user->id,
            'changes_new' => $changes,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Configuration IA mise à jour avec succès.');
    }
}