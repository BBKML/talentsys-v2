<?php

namespace App\Http\Controllers;

use App\Models\Statut;
use App\Models\TypeDocument;
use App\Models\TypeNote;
use App\Models\TypeFrais;
use App\Models\ModePaiement;
use App\Models\TypeAbonnement;
use App\Models\ArticleType;
use App\Models\Account;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ParametresController extends Controller
{
    private function etabId()
    {
        return session('etablissement_id', 1);
    }

    public function index()
    {
        $etabId = $this->etabId();

        $statuts = Statut::all();
        $typesDocument = TypeDocument::where('id_etablissement', $etabId)->get();
        $typesNote = TypeNote::where('id_etablissement', $etabId)->get();
        $typesFrais = TypeFrais::where('id_etablissement', $etabId)->get();
        $modesPaiement = ModePaiement::where('id_etablissement', $etabId)->get();
        $typesAbonnement = TypeAbonnement::where('id_etablissement', $etabId)->get();
        $articleTypes = ArticleType::all(); // Managed globally

        $user = Auth::user();
        $account = $user ? $user->account : null;

        return view('parametres.index', compact(
            'statuts', 'typesDocument', 'typesNote', 'typesFrais', 
            'modesPaiement', 'typesAbonnement', 'articleTypes', 'account', 'user'
        ));
    }

    // ─── MON COMPTE ───
    public function updateCompte(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'mail' => 'required|email'
        ]);

        $user = Auth::user();
        $account = $user ? $user->account : null;

        if ($account) {
            $account->update([
                'nom' => trim($request->nom),
                'prenom' => trim($request->prenom)
            ]);
        }

        if ($user && strtolower(trim($request->mail)) !== $user->mail) {
            // Check email uniqueness
            $exists = Utilisateur::where('mail', strtolower(trim($request->mail)))->where('id', '!=', $user->id)->exists();
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Cette adresse e-mail est déjà utilisée.'], 422);
            }
            $user->update([
                'mail' => strtolower(trim($request->mail))
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Profil mis à jour avec succès.']);
    }

    // ─── MOT DE PASSE ───
    public function updatePassword(Request $request)
    {
        $request->validate([
            'ancien' => 'required',
            'nouveau' => 'required|min:6',
            'confirmer' => 'required|same:nouveau'
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié.'], 401);
        }

        if ($user->mot_de_passe !== $request->ancien) {
            return response()->json(['success' => false, 'message' => 'Mot de passe actuel incorrect.'], 422);
        }

        $user->update([
            'mot_de_passe' => $request->nouveau
        ]);

        return response()->json(['success' => true, 'message' => 'Mot de passe modifié avec succès.']);
    }

    // ─── REFERENTIALS CRUD ───

    // Statuts
    public function storeStatut(Request $request)
    {
        $request->validate(['libelle' => 'required|string']);
        $statut = Statut::create(['libelle' => $request->libelle]);
        return response()->json(['success' => true, 'message' => 'Statut créé.', 'data' => $statut]);
    }
    public function updateStatut(Request $request, $id)
    {
        $request->validate(['libelle' => 'required|string']);
        $statut = Statut::findOrFail($id);
        $statut->update(['libelle' => $request->libelle]);
        return response()->json(['success' => true, 'message' => 'Statut mis à jour.', 'data' => $statut]);
    }
    public function destroyStatut($id)
    {
        Statut::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Statut supprimé.']);
    }

    // Types Document
    public function storeTypeDocument(Request $request)
    {
        $request->validate(['libelle' => 'required|string']);
        $doc = TypeDocument::create([
            'libelle' => $request->libelle,
            'obligatoire' => $request->boolean('obligatoire'),
            'id_statut' => 1,
            'id_etablissement' => $this->etabId()
        ]);
        return response()->json(['success' => true, 'message' => 'Type de document créé.', 'data' => $doc]);
    }
    public function updateTypeDocument(Request $request, $id)
    {
        $request->validate(['libelle' => 'required|string']);
        $doc = TypeDocument::where('id_etablissement', $this->etabId())->findOrFail($id);
        $doc->update([
            'libelle' => $request->libelle,
            'obligatoire' => $request->boolean('obligatoire')
        ]);
        return response()->json(['success' => true, 'message' => 'Type de document mis à jour.', 'data' => $doc]);
    }
    public function destroyTypeDocument($id)
    {
        TypeDocument::where('id_etablissement', $this->etabId())->findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Type de document supprimé.']);
    }

    // Types Note
    public function storeTypeNote(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string',
            'pourcentage' => 'required|numeric|min:0|max:100'
        ]);
        $note = TypeNote::create([
            'libelle' => $request->libelle,
            'type_systeme' => false,
            'pourcentage' => $request->pourcentage,
            'id_statut' => 1,
            'id_etablissement' => $this->etabId()
        ]);
        return response()->json(['success' => true, 'message' => 'Type de note créé.', 'data' => $note]);
    }
    public function updateTypeNote(Request $request, $id)
    {
        $request->validate([
            'libelle' => 'required|string',
            'pourcentage' => 'required|numeric|min:0|max:100'
        ]);
        $note = TypeNote::where('id_etablissement', $this->etabId())->findOrFail($id);
        $note->update([
            'libelle' => $request->libelle,
            'pourcentage' => $request->pourcentage
        ]);
        return response()->json(['success' => true, 'message' => 'Type de note mis à jour.', 'data' => $note]);
    }
    public function destroyTypeNote($id)
    {
        TypeNote::where('id_etablissement', $this->etabId())->findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Type de note supprimé.']);
    }

    // Types Frais
    public function storeTypeFrais(Request $request)
    {
        $request->validate(['libelle' => 'required|string']);
        $frais = TypeFrais::create([
            'libelle' => $request->libelle,
            'obligatoire' => $request->boolean('obligatoire'),
            'id_statut' => 1,
            'id_etablissement' => $this->etabId()
        ]);
        return response()->json(['success' => true, 'message' => 'Type de frais créé.', 'data' => $frais]);
    }
    public function updateTypeFrais(Request $request, $id)
    {
        $request->validate(['libelle' => 'required|string']);
        $frais = TypeFrais::where('id_etablissement', $this->etabId())->findOrFail($id);
        $frais->update([
            'libelle' => $request->libelle,
            'obligatoire' => $request->boolean('obligatoire')
        ]);
        return response()->json(['success' => true, 'message' => 'Type de frais mis à jour.', 'data' => $frais]);
    }
    public function destroyTypeFrais($id)
    {
        TypeFrais::where('id_etablissement', $this->etabId())->findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Type de frais supprimé.']);
    }

    // Modes Paiement
    public function storeModePaiement(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string',
            'sigle' => 'required|string'
        ]);
        $mode = ModePaiement::create([
            'libelle' => $request->libelle,
            'sigle' => strtoupper($request->sigle),
            'id_statut' => 1,
            'id_etablissement' => $this->etabId()
        ]);
        return response()->json(['success' => true, 'message' => 'Mode de paiement créé.', 'data' => $mode]);
    }
    public function updateModePaiement(Request $request, $id)
    {
        $request->validate([
            'libelle' => 'required|string',
            'sigle' => 'required|string'
        ]);
        $mode = ModePaiement::where('id_etablissement', $this->etabId())->findOrFail($id);
        $mode->update([
            'libelle' => $request->libelle,
            'sigle' => strtoupper($request->sigle)
        ]);
        return response()->json(['success' => true, 'message' => 'Mode de paiement mis à jour.', 'data' => $mode]);
    }
    public function destroyModePaiement($id)
    {
        ModePaiement::where('id_etablissement', $this->etabId())->findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Mode de paiement supprimé.']);
    }

    // Types Abonnement
    public function storeTypeAbonnement(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string',
            'nb_utilisateurs_max' => 'required|integer|min:1',
            'nb_etudiants_max' => 'required|integer|min:1',
            'prix_mensuel' => 'required|numeric|min:0'
        ]);
        $ab = TypeAbonnement::create([
            'libelle' => $request->libelle,
            'nb_utilisateurs_max' => $request->nb_utilisateurs_max,
            'nb_etudiants_max' => $request->nb_etudiants_max,
            'prix_mensuel' => $request->prix_mensuel,
            'id_statut' => 1,
            'id_etablissement' => $this->etabId()
        ]);
        return response()->json(['success' => true, 'message' => 'Type d\'abonnement créé.', 'data' => $ab]);
    }
    public function updateTypeAbonnement(Request $request, $id)
    {
        $request->validate([
            'libelle' => 'required|string',
            'nb_utilisateurs_max' => 'required|integer|min:1',
            'nb_etudiants_max' => 'required|integer|min:1',
            'prix_mensuel' => 'required|numeric|min:0'
        ]);
        $ab = TypeAbonnement::where('id_etablissement', $this->etabId())->findOrFail($id);
        $ab->update([
            'libelle' => $request->libelle,
            'nb_utilisateurs_max' => $request->nb_utilisateurs_max,
            'nb_etudiants_max' => $request->nb_etudiants_max,
            'prix_mensuel' => $request->prix_mensuel
        ]);
        return response()->json(['success' => true, 'message' => 'Type d\'abonnement mis à jour.', 'data' => $ab]);
    }
    public function destroyTypeAbonnement($id)
    {
        TypeAbonnement::where('id_etablissement', $this->etabId())->findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Type d\'abonnement supprimé.']);
    }

    // Types Articles (Stock category)
    public function storeTypeArticle(Request $request)
    {
        $request->validate(['libelle' => 'required|string']);
        $type = ArticleType::create([
            'libelle_article_types' => $request->libelle,
            'slug_article_types' => Str::slug($request->libelle)
        ]);
        return response()->json(['success' => true, 'message' => 'Type d\'article créé.', 'data' => $type]);
    }
    public function updateTypeArticle(Request $request, $id)
    {
        $request->validate(['libelle' => 'required|string']);
        $type = ArticleType::findOrFail($id);
        $type->update([
            'libelle_article_types' => $request->libelle,
            'slug_article_types' => Str::slug($request->libelle)
        ]);
        return response()->json(['success' => true, 'message' => 'Type d\'article mis à jour.', 'data' => $type]);
    }
    public function destroyTypeArticle($id)
    {
        ArticleType::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Type d\'article supprimé.']);
    }
}
