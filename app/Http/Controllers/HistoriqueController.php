<?php

namespace App\Http\Controllers;

use App\Models\Historique;
use Illuminate\Support\Facades\DB;

class HistoriqueController extends Controller
{
    public function index()
    {
        $etabId = session('etablissement_id');

        $activites = Historique::with('account')
            ->where('id_etablissement', $etabId)
            ->orderByDesc('date')
            ->orderByDesc('heure')
            ->limit(200)
            ->get();

        $connexions = DB::table('historique_connexion as hc')
            ->join('utilisateur as u', 'u.id', '=', 'hc.id_utilisateur')
            ->leftJoin('account as a', 'a.id_utilisateur', '=', 'u.id')
            ->where('hc.id_etablissement', $etabId)
            ->orderByDesc('hc.date_heure_login')
            ->limit(100)
            ->selectRaw("hc.id, u.mail,
                COALESCE(a.prenom||' '||a.nom,'Inconnu') as nom,
                hc.date_heure_login as login,
                hc.date_heure_logout as logout")
            ->get();

        return view('utilisateurs.historique', compact('activites', 'connexions'));
    }
}
