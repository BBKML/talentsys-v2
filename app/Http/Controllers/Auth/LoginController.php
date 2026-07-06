<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'mail'     => 'required|email',
            'password' => 'required',
        ], [
            'mail.required'     => 'Veuillez saisir votre email.',
            'mail.email'        => 'Format d\'email invalide.',
            'password.required' => 'Veuillez saisir votre mot de passe.',
        ]);

        $credentials = [
            'mail'     => strtolower(trim($request->mail)),
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Charger l'établissement depuis la session ou celui assigné
            $etablissementId = session('etablissement_id');
            if (!$etablissementId && $user->id_etablissement) {
                $etablissementId = $user->id_etablissement;
            }

            if ($etablissementId) {
                $etab = Etablissement::find($etablissementId);
                if ($etab) {
                    session(['etablissement_id' => $etab->id, 'etablissement' => $etab->toArray()]);
                    return redirect()->route('dashboard');
                }
            }

            // Si admin multi-établissement → sélection établissement
            return redirect()->route('etablissement.select');
        }

        return back()
            ->withInput($request->only('mail'))
            ->withErrors(['mail' => 'Email introuvable, compte inactif ou mot de passe incorrect.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
