<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Http\Request;

class DirectionController extends Controller
{
    public function index()
    {
        $etabId    = session('etablissement_id');
        $membres   = Direction::with(['account', 'role', 'utilisateur'])
            ->where('id_etablissement', $etabId)
            ->orderBy('id')
            ->get();
        $users     = Utilisateur::with('account')
            ->where('id_etablissement', $etabId)
            ->where('id_statut', 1)
            ->get();
        $roles     = Role::where('id_etablissement', $etabId)->orderBy('libelle')->get();

        return view('utilisateurs.direction', compact('membres', 'users', 'roles'));
    }

    public function store(Request $request)
    {
        $etabId = session('etablissement_id');
        $request->validate([
            'id_utilisateur' => 'required|integer',
            'id_role'        => 'required|integer',
        ]);

        $user  = Utilisateur::with('account')->find($request->id_utilisateur);
        $role  = Role::find($request->id_role);

        Direction::create([
            'id_utilisateur'   => $request->id_utilisateur,
            'id_account'       => $user?->account?->id,
            'id_etablissement' => $etabId,
            'id_role'          => $request->id_role,
            'id_statut'        => 1,
        ]);

        return redirect()->route('direction.index')->with('success', 'Membre ajouté.');
    }

    public function update(Request $request, $id)
    {
        $etabId = session('etablissement_id');
        $membre = Direction::where('id_etablissement', $etabId)->findOrFail($id);

        $membre->update(['id_role' => $request->id_role]);

        return redirect()->route('direction.index')->with('success', 'Membre mis à jour.');
    }

    public function destroy($id)
    {
        $etabId = session('etablissement_id');
        Direction::where('id_etablissement', $etabId)->findOrFail($id)->delete();
        return redirect()->route('direction.index')->with('success', 'Membre retiré.');
    }
}
