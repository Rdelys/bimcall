<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de login
     */
    public function showLogin()
    {
        if (session('authenticated')) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    /**
     * Vérifier le code et créer la session
     */
    public function login(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $allowedCode = config('services.app_auth.code');
        $inputCode   = trim($request->code);

        if ($allowedCode && $inputCode === trim($allowedCode)) {
            $request->session()->put('authenticated', true);
            $request->session()->regenerate();

            return redirect()->route('home')->with('success', 'Connexion réussie.');
        }

        return back()->withErrors(['code' => 'Code incorrect.'])->withInput();
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        $request->session()->forget('authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Déconnecté.');
    }
}