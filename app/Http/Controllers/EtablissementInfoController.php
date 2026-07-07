<?php

namespace App\Http\Controllers;

use App\Models\Couleur;
use App\Models\Etablissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EtablissementInfoController extends Controller
{
    // ── Informations ───────────────────────────────────────────────────────────
    public function informations()
    {
        $etab = Etablissement::findOrFail(session('etablissement_id'));
        $criteres = ['Validation des UE', 'Moyenne générale ≥ 10', 'Validation par semestre'];
        return view('etablissement.informations', compact('etab', 'criteres'));
    }

    public function updateInformations(Request $request)
    {
        $etabId = session('etablissement_id');
        $etab   = Etablissement::findOrFail($etabId);

        $request->validate([
            'nom'               => 'required|string|max:255',
            'code'              => 'nullable|string|max:50',
            'adresse'           => 'nullable|string|max:255',
            'contact_1'         => 'nullable|string|max:50',
            'contact_2'         => 'nullable|string|max:50',
            'email_1'           => 'nullable|email|max:255',
            'email_2'           => 'nullable|email|max:255',
            'systeme_academique'=> 'required|in:LMD,Grandes Écoles,BTS,Autres',
            'logo'              => 'nullable|image|max:2048',
        ]);

        $data = $request->only([
            'nom', 'code', 'adresse', 'contact_1', 'contact_2',
            'email_1', 'email_2', 'systeme_academique',
        ]);
        $data['siege'] = $request->boolean('siege');

        if ($request->hasFile('logo')) {
            if ($etab->logo) Storage::disk('public')->delete($etab->logo);
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $etab->update($data);

        $session = session('etablissement', []);
        $session['nom']  = $etab->nom;
        $session['code'] = $etab->code;
        $session['logo'] = $etab->logo ? Storage::url($etab->logo) : null;
        session(['etablissement' => $session]);

        return back()->with('success', 'Informations mises à jour avec succès.');
    }

    public function updateLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $etabId = session('etablissement_id');
        $etab   = Etablissement::findOrFail($etabId);

        if ($etab->logo) Storage::disk('public')->delete($etab->logo);
        $path = $request->file('logo')->store('logos', 'public');
        $etab->update(['logo' => $path]);

        $session = session('etablissement', []);
        $session['logo'] = Storage::url($path);
        session(['etablissement' => $session]);

        return back()->with('success', 'Logo mis à jour avec succès.');
    }

    public function updateLogoById(Request $request, $id)
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $etab = Etablissement::findOrFail($id);

        if ($etab->logo) Storage::disk('public')->delete($etab->logo);
        $path = $request->file('logo')->store('logos', 'public');
        $etab->update(['logo' => $path]);

        if (session('etablissement_id') == $id) {
            $session = session('etablissement', []);
            $session['logo'] = Storage::url($path);
            session(['etablissement' => $session]);
        }

        return back()->with('success', 'Logo mis à jour.');
    }

    // ── Couleurs ───────────────────────────────────────────────────────────────
    public function couleurs()
    {
        $etabId  = session('etablissement_id');
        $couleurs = Couleur::where('id_etablissement', $etabId)->orderBy('id')->get();
        return view('etablissement.couleurs', compact('couleurs'));
    }

    public function saveCouleurs(Request $request)
    {
        $etabId = session('etablissement_id');

        $request->validate([
            'couleurs'              => 'required|array',
            'couleurs.*.libelle'    => 'nullable|string|max:100',
            'couleurs.*.cle'        => 'nullable|string|max:50',
            'couleurs.*.code_hex'   => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        foreach ($request->couleurs as $item) {
            if (!empty($item['id'])) {
                Couleur::where('id', $item['id'])->where('id_etablissement', $etabId)
                    ->update([
                        'libelle'  => $item['libelle'] ?? null,
                        'cle'      => $item['cle'] ?? null,
                        'code_hex' => $item['code_hex'],
                    ]);
            } else {
                Couleur::create([
                    'libelle'          => $item['libelle'] ?? null,
                    'cle'              => $item['cle'] ?? null,
                    'code_hex'         => $item['code_hex'],
                    'id_etablissement' => $etabId,
                ]);
            }
        }

        return back()->with('success', 'Couleur enregistrée avec succès.');
    }

    public function updateCouleur(Request $request, $id)
    {
        $etabId = session('etablissement_id');
        $request->validate([
            'couleurs.0.code_hex' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);
        $item = $request->couleurs[0];
        Couleur::where('id', $id)->where('id_etablissement', $etabId)->update([
            'libelle'  => $item['libelle'] ?? null,
            'cle'      => $item['cle'] ?? null,
            'code_hex' => $item['code_hex'],
        ]);
        return back()->with('success', 'Couleur modifiée avec succès.');
    }

    public function deleteCouleur($id)
    {
        $etabId = session('etablissement_id');
        Couleur::where('id', $id)->where('id_etablissement', $etabId)->delete();
        return back()->with('success', 'Couleur supprimée.');
    }
}
