<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SallesController extends Controller
{
    public function index()
    {
        $etabId = session('etablissement_id');
        $salles = Salle::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        return view('etablissement.salles', compact('salles'));
    }

    public function store(Request $request)
    {
        $etabId = session('etablissement_id');

        $request->validate([
            'libelle' => 'required|string|max:255',
            'code'    => 'nullable|string|max:50',
            'type'    => 'nullable|string|max:100',
        ]);

        Salle::create([
            'libelle'          => $request->libelle,
            'code'             => $request->code,
            'type'             => $request->type,
            'id_statut'        => 1,
            'id_etablissement' => $etabId,
        ]);

        return back()->with('success', 'Salle créée avec succès.');
    }

    public function update(Request $request, $id)
    {
        $etabId = session('etablissement_id');

        $request->validate([
            'libelle' => 'required|string|max:255',
            'code'    => 'nullable|string|max:50',
            'type'    => 'nullable|string|max:100',
        ]);

        Salle::where('id', $id)->where('id_etablissement', $etabId)->update([
            'libelle' => $request->libelle,
            'code'    => $request->code,
            'type'    => $request->type,
        ]);

        return back()->with('success', 'Salle modifiée avec succès.');
    }

    public function destroy($id)
    {
        $etabId = session('etablissement_id');
        Salle::where('id', $id)->where('id_etablissement', $etabId)->delete();
        return back()->with('success', 'Salle supprimée.');
    }

    public function toggleStatut($id)
    {
        $etabId = session('etablissement_id');
        $salle  = Salle::where('id', $id)->where('id_etablissement', $etabId)->firstOrFail();
        $salle->update(['id_statut' => $salle->id_statut == 1 ? 2 : 1]);
        return back()->with('success', 'Statut mis à jour.');
    }
}
