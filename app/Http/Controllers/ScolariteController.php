<?php

namespace App\Http\Controllers;

use App\Models\Bourse;
use App\Models\FraisScolarite;
use App\Models\ModalitePaiement;
use App\Models\TypeFrais;
use App\Models\AnneeScolaire;
use App\Models\Filiere;
use App\Models\Niveau;
use Illuminate\Http\Request;

class ScolariteController extends Controller
{
    private function etabId() { return session('etablissement_id'); }

    // ══ BOURSES ════════════════════════════════════════════════════════════════

    public function bourses()
    {
        $bourses = Bourse::where('id_etablissement', $this->etabId())->orderBy('libelle')->get();
        return view('scolarite.bourses', compact('bourses'));
    }

    public function storeBourse(Request $r)
    {
        $r->validate(['libelle' => 'required|string|max:255', 'type_bourse' => 'required|string', 'valeur' => 'required|numeric|min:0']);
        Bourse::create([
            'libelle'          => $r->libelle,
            'type_bourse'      => $r->type_bourse,
            'valeur'           => $r->valeur,
            'id_statut'        => 1,
            'id_etablissement' => $this->etabId(),
        ]);
        return back()->with('success', 'Bourse créée.');
    }

    public function updateBourse(Request $r, $id)
    {
        $r->validate(['libelle' => 'required|string|max:255', 'type_bourse' => 'required|string', 'valeur' => 'required|numeric|min:0']);
        Bourse::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'libelle'     => $r->libelle,
            'type_bourse' => $r->type_bourse,
            'valeur'      => $r->valeur,
        ]);
        return back()->with('success', 'Bourse modifiée.');
    }

    public function destroyBourse($id)
    {
        Bourse::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Bourse supprimée.');
    }

    // ══ FRAIS DE SCOLARITÉ ════════════════════════════════════════════════════

    public function frais()
    {
        $etabId      = $this->etabId();
        $anneeActive = AnneeScolaire::where('id_etablissement', $etabId)->where('active', true)->first();
        $frais       = FraisScolarite::with(['typeFrais', 'niveau', 'annee'])
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('id')->get();
        $typesFrais  = TypeFrais::where('id_etablissement', $etabId)->where('id_statut', 1)->orderBy('libelle')->get();
        $niveaux     = Niveau::where('id_etablissement', $etabId)->where('id_statut', 1)->orderBy('ordre')->get();
        $annees      = AnneeScolaire::where('id_etablissement', $etabId)->orderByDesc('id')->get();
        return view('scolarite.frais', compact('frais', 'typesFrais', 'niveaux', 'annees', 'anneeActive'));
    }

    public function storeFrais(Request $r)
    {
        $r->validate(['id_type_frais' => 'required|integer', 'id_niveau' => 'required|integer', 'montant' => 'required|numeric|min:0']);
        $etabId      = $this->etabId();
        $anneeActive = AnneeScolaire::where('id_etablissement', $etabId)->where('active', true)->first();
        FraisScolarite::create([
            'id_type_frais'    => $r->id_type_frais,
            'id_niveau'        => $r->id_niveau,
            'id_annee_scolaire'=> $anneeActive?->id,
            'montant'          => $r->montant,
            'id_statut'        => 1,
            'id_etablissement' => $etabId,
        ]);
        return back()->with('success', 'Frais créé.');
    }

    public function updateFrais(Request $r, $id)
    {
        $r->validate(['id_type_frais' => 'required|integer', 'id_niveau' => 'required|integer', 'montant' => 'required|numeric|min:0']);
        FraisScolarite::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'id_type_frais' => $r->id_type_frais,
            'id_niveau'     => $r->id_niveau,
            'montant'       => $r->montant,
        ]);
        return back()->with('success', 'Frais modifié.');
    }

    public function destroyFrais($id)
    {
        FraisScolarite::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Frais supprimé.');
    }

    // ══ TYPES DE FRAIS ════════════════════════════════════════════════════════

    public function storeTypeFrais(Request $r)
    {
        $r->validate(['libelle' => 'required|string|max:255']);
        $tf = TypeFrais::create([
            'libelle'          => $r->libelle,
            'obligatoire'      => false,
            'id_statut'        => 1,
            'id_etablissement' => $this->etabId(),
        ]);
        return response()->json(['id' => $tf->id, 'libelle' => $tf->libelle]);
    }

    // ══ MODALITÉS DE PAIEMENT ════════════════════════════════════════════════

    public function modalites()
    {
        $etabId     = $this->etabId();
        $anneeActive = AnneeScolaire::where('id_etablissement', $etabId)->where('active', true)->first();
        $modalites  = ModalitePaiement::with(['fraisScolarite.typeFrais', 'fraisScolarite.niveau'])
            ->where('modalite_paiement.id_etablissement', $etabId)
            ->when($anneeActive, function ($q) use ($anneeActive) {
                $q->whereHas('fraisScolarite', fn($qf) => $qf->where('id_annee_scolaire', $anneeActive->id));
            })
            ->orderBy('id_frais_scolarite')->orderBy('id')
            ->get();
        $frais      = FraisScolarite::with(['typeFrais', 'niveau'])
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();
        $niveaux    = Niveau::where('id_etablissement', $etabId)->where('id_statut', 1)->orderBy('ordre')->get();
        $filieres   = Filiere::where('id_etablissement', $etabId)->where('id_statut', 1)->orderBy('libelle')->get();
        return view('scolarite.modalites', compact('modalites', 'frais', 'niveaux', 'filieres', 'anneeActive'));
    }

    public function storeModalite(Request $r)
    {
        $r->validate(['id_frais_scolarite' => 'required|integer', 'tranche' => 'required|string|max:100', 'pourcentage' => 'nullable|numeric|min:0|max:100']);
        ModalitePaiement::create([
            'id_frais_scolarite' => $r->id_frais_scolarite,
            'tranche'            => $r->tranche,
            'pourcentage'        => $r->pourcentage ?: 0,
            'date_debut'         => $r->date_debut ?: null,
            'date_fin'           => $r->date_fin ?: null,
            'id_statut'          => 1,
            'id_etablissement'   => $this->etabId(),
        ]);
        return back()->with('success', 'Modalité créée.');
    }

    public function updateModalite(Request $r, $id)
    {
        $r->validate(['id_frais_scolarite' => 'required|integer', 'tranche' => 'required|string|max:100', 'pourcentage' => 'nullable|numeric|min:0|max:100']);
        ModalitePaiement::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'id_frais_scolarite' => $r->id_frais_scolarite,
            'tranche'            => $r->tranche,
            'pourcentage'        => $r->pourcentage ?: 0,
            'date_debut'         => $r->date_debut ?: null,
            'date_fin'           => $r->date_fin ?: null,
        ]);
        return back()->with('success', 'Modalité modifiée.');
    }

    public function destroyModalite($id)
    {
        ModalitePaiement::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Modalité supprimée.');
    }
}
