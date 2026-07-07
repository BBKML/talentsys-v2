<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\CompteComptable;
use App\Models\Fournisseur;
use App\Models\BonCommande;
use App\Models\LigneCommande;
use App\Models\BonReception;
use App\Models\FactureFournisseur;
use App\Models\SignatureElectronique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AchatsController extends Controller
{
    private function etabId()
    {
        return session('etablissement_id', 1);
    }

    private function getBaseData()
    {
        $etabId = $this->etabId();

        $fournisseurs = Fournisseur::with('compte')
            ->where('id_etablissement', $etabId)
            ->get();

        $planComptable = CompteComptable::where('id_etablissement', $etabId)
            ->where('actif', true)
            ->where('classe', 6)
            ->get();

        $bonsCommande = BonCommande::with(['fournisseur', 'lignes'])
            ->where('id_etablissement', $etabId)
            ->get();

        $bonsReception = BonReception::with('bonCommande')
            ->where('id_etablissement', $etabId)
            ->get();

        $factures = FactureFournisseur::with(['bonCommande', 'fournisseur'])
            ->where('id_etablissement', $etabId)
            ->get();

        $signatures = SignatureElectronique::where('id_etablissement', $etabId)
            ->get();

        return compact('fournisseurs', 'planComptable', 'bonsCommande', 'bonsReception', 'factures', 'signatures');
    }

    public function index()
    {
        $data = $this->getBaseData();
        return view('achats.index', $data);
    }

    // ─── FOURNISSEURS ────────────────────────────────────────────────────────

    public function storeFournisseur(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'numero_contribuable' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email',
            'adresse' => 'nullable|string',
            'id_compte_charge' => 'nullable|integer',
            'actif' => 'required|boolean'
        ]);

        $fournisseur = Fournisseur::create([
            'id_etablissement' => $this->etabId(),
            'nom' => $request->nom,
            'numero_contribuable' => $request->numero_contribuable,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'adresse' => $request->adresse,
            'id_compte_charge' => $request->id_compte_charge ?: null,
            'actif' => $request->actif
        ]);

        return response()->json(['success' => true, 'message' => 'Fournisseur créé.', 'data' => $fournisseur]);
    }

    public function updateFournisseur(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string',
            'numero_contribuable' => 'nullable|string',
            'telephone' => 'nullable|string',
            'email' => 'nullable|email',
            'adresse' => 'nullable|string',
            'id_compte_charge' => 'nullable|integer',
            'actif' => 'required|boolean'
        ]);

        $fournisseur = Fournisseur::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $fournisseur->update([
            'nom' => $request->nom,
            'numero_contribuable' => $request->numero_contribuable,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'adresse' => $request->adresse,
            'id_compte_charge' => $request->id_compte_charge ?: null,
            'actif' => $request->actif
        ]);

        return response()->json(['success' => true, 'message' => 'Fournisseur mis à jour.', 'data' => $fournisseur]);
    }

    public function destroyFournisseur($id)
    {
        $fournisseur = Fournisseur::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $fournisseur->delete();
        return response()->json(['success' => true, 'message' => 'Fournisseur supprimé.']);
    }

    // ─── BONS DE COMMANDE ───────────────────────────────────────────────────

    public function storeCommande(Request $request)
    {
        $request->validate([
            'id_fournisseur' => 'required|integer',
            'date_commande' => 'required|date',
            'statut' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $etabId = $this->etabId();
        
        // Auto generation de numero
        $year = date('Y');
        $count = BonCommande::where('id_etablissement', $etabId)->whereYear('date_commande', $year)->count() + 1;
        $numero = 'BC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $bc = BonCommande::create([
            'id_etablissement' => $etabId,
            'id_fournisseur' => $request->id_fournisseur,
            'numero' => $numero,
            'date_commande' => $request->date_commande,
            'statut' => $request->statut,
            'notes' => $request->notes,
            'total' => 0
        ]);

        return response()->json(['success' => true, 'message' => 'Bon de commande créé.', 'data' => $bc]);
    }

    public function updateCommande(Request $request, $id)
    {
        $request->validate([
            'id_fournisseur' => 'required|integer',
            'date_commande' => 'required|date',
            'statut' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $bc = BonCommande::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $bc->update([
            'id_fournisseur' => $request->id_fournisseur,
            'date_commande' => $request->date_commande,
            'statut' => $request->statut,
            'notes' => $request->notes
        ]);

        return response()->json(['success' => true, 'message' => 'Bon de commande mis à jour.', 'data' => $bc]);
    }

    public function destroyCommande($id)
    {
        $bc = BonCommande::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        
        DB::beginTransaction();
        try {
            LigneCommande::where('id_bon_commande', $bc->id)->delete();
            $bc->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Bon de commande supprimé.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── LIGNES DE COMMANDE ─────────────────────────────────────────────────

    public function addLigne(Request $request, $idCommande)
    {
        $request->validate([
            'designation' => 'required|string',
            'quantite' => 'required|numeric|min:0.01',
            'prix_unitaire' => 'required|numeric|min:0'
        ]);

        $etabId = $this->etabId();
        $bc = BonCommande::where('id', $idCommande)->where('id_etablissement', $etabId)->firstOrFail();

        DB::beginTransaction();
        try {
            $ligne = LigneCommande::create([
                'id_etablissement' => $etabId,
                'id_bon_commande' => $bc->id,
                'designation' => $request->designation,
                'quantite' => $request->quantite,
                'prix_unitaire' => $request->prix_unitaire,
                'montant' => $request->quantite * $request->prix_unitaire
            ]);

            // Update total bon
            $bc->total = LigneCommande::where('id_bon_commande', $bc->id)->sum('montant');
            $bc->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Ligne ajoutée.', 'ligne' => $ligne, 'total' => $bc->total]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function removeLigne($idCommande, $idLigne)
    {
        $etabId = $this->etabId();
        $bc = BonCommande::where('id', $idCommande)->where('id_etablissement', $etabId)->firstOrFail();
        $ligne = LigneCommande::where('id', $idLigne)->where('id_bon_commande', $bc->id)->firstOrFail();

        DB::beginTransaction();
        try {
            $ligne->delete();
            $bc->total = LigneCommande::where('id_bon_commande', $bc->id)->sum('montant');
            $bc->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Ligne supprimée.', 'total' => $bc->total]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── BONS DE RÉCEPTION ──────────────────────────────────────────────────

    public function storeReception(Request $request)
    {
        $request->validate([
            'id_bon_commande' => 'required|integer',
            'date_reception' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $br = BonReception::create([
                'id_etablissement' => $this->etabId(),
                'id_bon_commande' => $request->id_bon_commande,
                'date_reception' => $request->date_reception,
                'notes' => $request->notes
            ]);

            // Mettre a jour le statut du bon de commande a recu
            $bc = BonCommande::find($request->id_bon_commande);
            if ($bc) {
                $bc->statut = 'recu';
                $bc->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Bon de réception enregistré.', 'data' => $br]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateReception(Request $request, $id)
    {
        $request->validate([
            'date_reception' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $br = BonReception::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $br->update([
            'date_reception' => $request->date_reception,
            'notes' => $request->notes
        ]);

        return response()->json(['success' => true, 'message' => 'Bon de réception mis à jour.', 'data' => $br]);
    }

    public function destroyReception($id)
    {
        $br = BonReception::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $br->delete();
        return response()->json(['success' => true, 'message' => 'Bon de réception supprimé.']);
    }

    // ─── FACTURES FOURNISSEURS ─────────────────────────────────────────────

    public function storeFacture(Request $request)
    {
        $request->validate([
            'numero_facture' => 'required|string',
            'id_fournisseur' => 'required|integer',
            'id_bon_commande' => 'nullable|integer',
            'montant' => 'required|numeric|min:0',
            'date_facture' => 'required|date',
            'date_echeance' => 'nullable|date',
            'statut' => 'required|string',
            'notes' => 'nullable|string',
            'url_document' => 'nullable|string'
        ]);

        $facture = FactureFournisseur::create([
            'id_etablissement' => $this->etabId(),
            'id_fournisseur' => $request->id_fournisseur,
            'id_bon_commande' => $request->id_bon_commande ?: null,
            'numero_facture' => $request->numero_facture,
            'montant' => $request->montant,
            'date_facture' => $request->date_facture,
            'date_echeance' => $request->date_echeance ?: null,
            'statut' => $request->statut,
            'notes' => $request->notes,
            'url_document' => $request->url_document ?: ''
        ]);

        return response()->json(['success' => true, 'message' => 'Facture enregistrée.', 'data' => $facture]);
    }

    public function updateFacture(Request $request, $id)
    {
        $request->validate([
            'numero_facture' => 'required|string',
            'id_fournisseur' => 'required|integer',
            'id_bon_commande' => 'nullable|integer',
            'montant' => 'required|numeric|min:0',
            'date_facture' => 'required|date',
            'date_echeance' => 'nullable|date',
            'statut' => 'required|string',
            'notes' => 'nullable|string',
            'url_document' => 'nullable|string'
        ]);

        $facture = FactureFournisseur::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $facture->update([
            'id_fournisseur' => $request->id_fournisseur,
            'id_bon_commande' => $request->id_bon_commande ?: null,
            'numero_facture' => $request->numero_facture,
            'montant' => $request->montant,
            'date_facture' => $request->date_facture,
            'date_echeance' => $request->date_echeance ?: null,
            'statut' => $request->statut,
            'notes' => $request->notes,
            'url_document' => $request->url_document ?: ''
        ]);

        return response()->json(['success' => true, 'message' => 'Facture mise à jour.', 'data' => $facture]);
    }

    public function destroyFacture($id)
    {
        $facture = FactureFournisseur::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $facture->delete();
        return response()->json(['success' => true, 'message' => 'Facture supprimée.']);
    }

    // ─── SIGNATURES ─────────────────────────────────────────────────────────

    public function storeSignature(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'fonction' => 'required|string',
            'url_signature' => 'nullable|string',
            'actif' => 'required|boolean'
        ]);

        $sig = SignatureElectronique::create([
            'id_etablissement' => $this->etabId(),
            'nom' => $request->nom,
            'fonction' => $request->fonction,
            'url_signature' => $request->url_signature ?: '',
            'actif' => $request->actif
        ]);

        return response()->json(['success' => true, 'message' => 'Signature enregistrée.', 'data' => $sig]);
    }

    public function updateSignature(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string',
            'fonction' => 'required|string',
            'url_signature' => 'nullable|string',
            'actif' => 'required|boolean'
        ]);

        $sig = SignatureElectronique::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $sig->update([
            'nom' => $request->nom,
            'fonction' => $request->fonction,
            'url_signature' => $request->url_signature ?: '',
            'actif' => $request->actif
        ]);

        return response()->json(['success' => true, 'message' => 'Signature mise à jour.', 'data' => $sig]);
    }

    public function destroySignature($id)
    {
        $sig = SignatureElectronique::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $sig->delete();
        return response()->json(['success' => true, 'message' => 'Signature supprimée.']);
    }
}
