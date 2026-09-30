<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.auth.login');
    }

    /**
     * Tentative de connexion
     */
    public function login(Request $request)
    {
        $validator = $this->validateData($request, 'login');
        
        if ($validator->fails()) {
            return back()->withErrors($validator->errors())->withInput();
        }

        $data = $validator->validated();

        // Recherche de l'utilisateur
        $user = User::where('email', $data['email'])->first();

        // Vérification du mot de passe
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Les identifiants sont incorrects.',
            ])->onlyInput('email');
        }

        $user->load('typeCompte');
        if (!in_array($user->typeCompte->id, [1, 2])) {
            return back()->withErrors([
                'email' => 'Accès réservé aux administrateurs.',
            ])->onlyInput('email');
        }

        // Connexion de l'utilisateur
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Redirection vers le dashboard
        if ($user->typeCompte->id == 1) {
            // Admin
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Connexion réussie ! Bienvenue ' . $user->name . ' !');
        } else {
            // Content Manager
            return redirect()->intended(route('admin.content.dashboard'))->with('success', 'Connexion réussie ! Bienvenue ' . $user->name . ' !');
        }
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login')
                        ->with('success', 'Vous avez été déconnecté avec succès.');
    }

    /**
     * Validation des données
     */
    private function validateData(Request $request, $type)
    {
        $rules = [];

        switch ($type) {
            case 'login':
                $rules = [
                    'email' => 'required|email',
                    'password' => 'required|string|min:6',
                ];
                break;
        }

        return Validator::make($request->all(), $rules);
    }
}