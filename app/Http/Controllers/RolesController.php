<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function index()
    {
        $etabId = session('etablissement_id');
        $roles  = Role::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        return view('utilisateurs.roles', compact('roles'));
    }

    public function store(Request $request)
    {
        $etabId = session('etablissement_id');
        $request->validate(['libelle' => 'required|string|max:100']);

        $perms = $this->extractPerms($request);
        $perms['libelle']           = trim($request->libelle);
        $perms['id_etablissement']  = $etabId;
        $perms['id_statut']         = 1;
        $perms['is_super_admin']    = $request->boolean('is_super_admin');

        Role::create($perms);
        return redirect()->route('roles.index')->with('success', 'Rôle créé.');
    }

    public function update(Request $request, $id)
    {
        $etabId = session('etablissement_id');
        $request->validate(['libelle' => 'required|string|max:100']);

        $role  = Role::where('id_etablissement', $etabId)->findOrFail($id);
        $perms = $this->extractPerms($request);
        $perms['libelle']        = trim($request->libelle);
        $perms['is_super_admin'] = $request->boolean('is_super_admin');

        $role->update($perms);
        return redirect()->route('roles.index')->with('success', 'Rôle mis à jour.');
    }

    public function destroy($id)
    {
        $etabId = session('etablissement_id');
        try {
            $role = Role::where('id_etablissement', $etabId)->findOrFail($id);
            $role->delete();
            return redirect()->route('roles.index')->with('success', 'Rôle supprimé.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Impossible de supprimer ce rôle (utilisateurs assignés).');
        }
    }

    private function extractPerms(Request $request): array
    {
        $cols = [
            'voir_academique','voir_enseignants','voir_etudiants',
            'voir_finance','voir_evaluations','voir_comptabilite',
            'voir_utilisateurs','voir_etablissement','voir_abonnements',
            'voir_achats','voir_ged',
        ];
        $data = [];
        foreach ($cols as $col) {
            $data[$col] = $request->boolean($col);
        }
        return $data;
    }
}
