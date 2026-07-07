<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EtablissementController extends Controller
{
    public function show()
    {
        $etablissements = Etablissement::orderBy('nom')->get();
        return view('auth.etablissement', compact('etablissements'));
    }

    public function select(Request $request)
    {
        $request->validate(['etablissement_id' => 'required|integer']);

        $etab = Etablissement::findOrFail($request->etablissement_id);
        $etabData = $etab->toArray();
        if (!empty($etabData['logo'])) {
            $etabData['logo'] = Storage::url($etabData['logo']);
        }
        session(['etablissement_id' => $etab->id, 'etablissement' => $etabData]);

        return redirect()->route('dashboard');
    }

    public function change()
    {
        session()->forget(['etablissement_id', 'etablissement']);
        return redirect()->route('etablissement.select');
    }
}
