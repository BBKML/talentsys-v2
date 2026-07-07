<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Direction;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UtilisateursController extends Controller
{
    public function index()
    {
        $etabId = session('etablissement_id');

        $utilisateurs = Utilisateur::with(['account', 'role'])
            ->where('id_etablissement', $etabId)
            ->orderBy('id', 'desc')
            ->get();

        $roles = Role::where('id_etablissement', $etabId)
            ->orderBy('libelle')
            ->get();

        return view('utilisateurs.index', compact('utilisateurs', 'roles'));
    }

    public function store(Request $request)
    {
        $etabId = session('etablissement_id');

        $request->validate([
            'mail'         => 'required|email',
            'mot_de_passe' => 'required|min:6',
            'id_role'      => 'required|integer',
            'nom'          => 'required|string',
            'prenom'       => 'required|string',
            'url_profil'   => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $user = Utilisateur::create([
                'mail'             => strtolower(trim($request->mail)),
                'mot_de_passe'     => $request->mot_de_passe,
                'id_role'          => $request->id_role,
                'id_statut'        => 1,
                'id_etablissement' => $etabId,
            ]);

            $photoPath = null;
            if ($request->hasFile('url_profil')) {
                $photoPath = $request->file('url_profil')->store('profiles', 'public');
            }

            $account = Account::create([
                'id_utilisateur'   => $user->id,
                'nom'              => strtoupper(trim($request->nom)),
                'prenom'           => ucfirst(strtolower(trim($request->prenom))),
                'sexe'             => $request->sexe ?? 'M',
                'contact'          => $request->contact,
                'nationalite'      => $request->nationalite,
                'date_naissance'   => $request->date_naissance ?: null,
                'lieu_naissance'   => $request->lieu_naissance,
                'url_profil'       => $photoPath,
                'id_statut'        => 1,
                'id_etablissement' => $etabId,
            ]);

            Direction::create([
                'id_utilisateur'   => $user->id,
                'id_account'       => $account->id,
                'id_etablissement' => $etabId,
                'id_role'          => $request->id_role,
                'id_statut'        => 1,
            ]);

            DB::commit();
            return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur créé avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Création utilisateur: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $etabId = session('etablissement_id');

        $request->validate([
            'mail'       => 'required|email',
            'id_role'    => 'required|integer',
            'nom'        => 'required|string',
            'prenom'     => 'required|string',
            'url_profil' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $user    = Utilisateur::where('id_etablissement', $etabId)->findOrFail($id);
            $oldRole = $user->id_role;

            $user->update([
                'mail'    => strtolower(trim($request->mail)),
                'id_role' => $request->id_role,
            ]);

            if ($request->filled('mot_de_passe')) {
                $user->update(['mot_de_passe' => $request->mot_de_passe]);
            }

            if ($user->account) {
                $accountData = [
                    'nom'            => strtoupper(trim($request->nom)),
                    'prenom'         => ucfirst(strtolower(trim($request->prenom))),
                    'sexe'           => $request->sexe ?? $user->account->sexe,
                    'contact'        => $request->contact ?? $user->account->contact,
                    'nationalite'    => $request->nationalite ?? $user->account->nationalite,
                    'date_naissance' => $request->date_naissance ?: $user->account->date_naissance,
                    'lieu_naissance' => $request->lieu_naissance ?? $user->account->lieu_naissance,
                ];

                if ($request->hasFile('url_profil')) {
                    if ($user->account->url_profil) {
                        Storage::disk('public')->delete($user->account->url_profil);
                    }
                    $accountData['url_profil'] = $request->file('url_profil')->store('profiles', 'public');
                }

                $user->account->update($accountData);
            }

            if ((int)$oldRole !== (int)$request->id_role) {
                Direction::where('id_utilisateur', $id)->update(['id_role' => $request->id_role]);
                DB::table('historique_connexion')
                    ->where('id_utilisateur', $id)
                    ->whereNull('date_heure_logout')
                    ->update(['date_heure_logout' => now()]);
            }

            DB::commit();
            return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur modifié avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $etabId = session('etablissement_id');
        try {
            $user = Utilisateur::where('id_etablissement', $etabId)->findOrFail($id);
            if ($user->account?->url_profil) {
                Storage::disk('public')->delete($user->account->url_profil);
            }
            Direction::where('id_utilisateur', $id)->delete();
            Account::where('id_utilisateur', $id)->delete();
            $user->delete();
            return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur supprimé.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Impossible de supprimer: ' . $e->getMessage());
        }
    }

    private function statutActifId(): int
    {
        return (int) (DB::table('statut')
            ->whereRaw("LOWER(libelle) LIKE '%actif%' OR LOWER(libelle) LIKE '%active%'")
            ->value('id')
            ?? DB::table('statut')->orderBy('id')->value('id')
            ?? 1);
    }

    private function statutInactifId(): ?int
    {
        return DB::table('statut')
            ->whereRaw("LOWER(libelle) LIKE '%inact%' OR LOWER(libelle) LIKE '%suspen%' OR LOWER(libelle) LIKE '%bloqu%'")
            ->value('id');
    }

    public function toggleStatut($id)
    {
        $etabId = session('etablissement_id');
        $user   = Utilisateur::where('id_etablissement', $etabId)->findOrFail($id);

        $actifId   = $this->statutActifId();
        $inactifId = $this->statutInactifId();

        if (!$inactifId) {
            return back()->with('error', 'Statut inactif introuvable en base de données.');
        }

        $newStatut = $user->id_statut == $actifId ? $inactifId : $actifId;
        $user->update(['id_statut' => $newStatut]);

        return back()->with('success', 'Statut mis à jour.');
    }
}
