<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Etablissement;
use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Inscription;
use App\Models\Niveau;
use App\Models\FraisScolarite;
use App\Models\TypeFrais;
use App\Models\ModalitePaiement;
use App\Models\ModePaiement;
use App\Models\EcheancierScolarite;
use App\Models\TranchePrevu;
use App\Models\Paiement;
use App\Models\PaiementTrancheDetail;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    private function etabId()
    {
        return session('etablissement_id');
    }

    private function anneeActive()
    {
        return AnneeScolaire::where('id_etablissement', $this->etabId())
            ->where('active', true)
            ->first();
    }

    private function etablissement()
    {
        return Etablissement::find($this->etabId());
    }

    private function getBaseData()
    {
        $etabId = $this->etabId();
        $anneeActive = $this->anneeActive();

        $niveaux = Niveau::where('id_etablissement', $etabId)->orderBy('ordre')->orderBy('libelle')->get();
        $filieres = Filiere::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $classes = Classe::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        
        $inscriptions = Inscription::with(['etudiant', 'classe.niveau', 'classe.filiere'])->where('id_etablissement', $etabId);
        if ($anneeActive) {
            $inscriptions->where('id_annee_scolaire', $anneeActive->id);
        }
        $inscriptions = $inscriptions->get();

        $fraisScolarite = FraisScolarite::where('id_etablissement', $etabId)->get();
        $typesFrais = TypeFrais::where('id_etablissement', $etabId)->get();
        $modalitesPaiement = ModalitePaiement::where('id_etablissement', $etabId)->get();
        $modesPaiement = ModePaiement::where('id_etablissement', $etabId)->get();

        $echeanciers = EcheancierScolarite::whereHas('inscription', function($q) use ($etabId) {
            $q->where('id_etablissement', $etabId);
        })->get();

        $tranchesPrevues = TranchePrevu::whereHas('echeancier.inscription', function($q) use ($etabId) {
            $q->where('id_etablissement', $etabId);
        })->get();

        $paiements = Paiement::whereHas('inscription', function($q) use ($etabId) {
            $q->where('id_etablissement', $etabId);
        })->get();

        $paiementTranchesDetail = PaiementTrancheDetail::whereHas('paiement.inscription', function($q) use ($etabId) {
            $q->where('id_etablissement', $etabId);
        })->get();

        $factures = Facture::whereHas('inscription', function($q) use ($etabId) {
            $q->where('id_etablissement', $etabId);
        })->get();

        // Statuts mock (since it's a simple lookup table, and we might not have the model directly)
        $statuts = \DB::table('statut')->get();
        $etudiants = Etudiant::where('id_etablissement', $etabId)->get();

        return compact(
            'niveaux', 'filieres', 'classes', 'inscriptions', 'etudiants',
            'fraisScolarite', 'typesFrais', 'modalitesPaiement', 'modesPaiement',
            'echeanciers', 'tranchesPrevues', 'paiements', 'paiementTranchesDetail',
            'factures', 'statuts', 'anneeActive'
        );
    }

    public function echeanciersIndex()
    {
        $data = $this->getBaseData();
        return view('finance.echeanciers', $data);
    }

    public function tranchesPrevuesIndex()
    {
        $data = $this->getBaseData();
        return view('finance.tranches_prevues', $data);
    }

    public function paiementsIndex()
    {
        $data = $this->getBaseData();
        return view('finance.paiements', $data);
    }

    public function facturesIndex()
    {
        $data = $this->getBaseData();
        return view('finance.factures', $data);
    }

    public function storePaiement(Request $request)
    {
        $request->validate([
            'id_inscription' => 'required|integer',
            'id_frais' => 'required|integer',
            'montant' => 'required|numeric|min:1',
            'id_mode' => 'required|integer',
            'date' => 'required|date',
            'reference' => 'nullable|string'
        ]);

        $etabId = session('etablissement_id', 1);

        DB::beginTransaction();
        try {
            // Rechercher l'échéancier
            $echeancier = EcheancierScolarite::where('id_inscription', $request->id_inscription)
                            ->where('id_frais_scolarite', $request->id_frais)
                            ->first();
                            
            if (!$echeancier) {
                return response()->json(['success' => false, 'message' => 'Échéancier introuvable.'], 404);
            }

            // Calcul du restant
            $tranches = TranchePrevu::where('id_echeancier_scolarite', $echeancier->id)
                            ->orderBy('date_echeance', 'asc')->get();
                            
            $payeTotal = PaiementTrancheDetail::whereIn('id_tranche_prevu', $tranches->pluck('id'))->sum('montant_alloue');
            $net = max(0, $echeancier->montant_total - $echeancier->montant_remise);
            $restant = max(0, $net - $payeTotal);

            // Bloquer la transaction si le paiement dépasse le reste
            if ($request->montant > $restant) {
                return response()->json(['success' => false, 'message' => 'Transaction bloquée : Le montant versé dépasse le solde restant ('.$restant.' FCFA).'], 422);
            }

            // Créer le paiement
            $paiement = new Paiement();
            $paiement->id_inscription = $request->id_inscription;
            $paiement->id_frais_scolarite = $request->id_frais;
            $paiement->montant_verse = $request->montant;
            $paiement->date = $request->date;
            $paiement->id_mode_paiement = $request->id_mode;
            $paiement->reference = $request->reference ?? 'PAY-'.date('Y').'-'.rand(100,999);
            $paiement->id_etablissement = $etabId;
            $paiement->save();

            // Allocation FIFO
            $montantAAllouer = $request->montant;
            foreach ($tranches as $t) {
                if ($montantAAllouer <= 0) break;
                
                $dejaPaye = PaiementTrancheDetail::where('id_tranche_prevu', $t->id)->sum('montant_alloue');
                $resteTranche = max(0, $t->montant - $dejaPaye);
                
                if ($resteTranche > 0) {
                    $allocation = min($montantAAllouer, $resteTranche);
                    
                    PaiementTrancheDetail::create([
                        'id_paiement' => $paiement->id,
                        'id_tranche_prevu' => $t->id,
                        'montant_alloue' => $allocation
                    ]);
                    
                    $montantAAllouer -= $allocation;
                    
                    // Mise à jour du statut de la tranche
                    $nouveauPaye = $dejaPaye + $allocation;
                    if ($nouveauPaye >= $t->montant) {
                        $t->statut_paiement = 'Payé';
                    } else {
                        $t->statut_paiement = 'Partiel';
                    }
                    $t->save();
                }
            }

            // Création automatique de la facture correspondante
            $factureExistante = Facture::where('id_inscription', $request->id_inscription)
                ->where('id_frais_scolarite', $request->id_frais)
                ->first();

            if (!$factureExistante) {
                Facture::create([
                    'id_inscription' => $request->id_inscription,
                    'id_frais_scolarite' => $request->id_frais,
                    'numero_facture' => 'FACT-FRA-'.date('Y').'-'.str_pad($request->id_inscription, 3, '0', STR_PAD_LEFT),
                    'montant_total' => $net,
                    'date_facture' => date('Y-m-d'),
                    'statut_facture' => 'Émise',
                    'id_etablissement' => $etabId
                ]);
                $factureExistante = Facture::where('id_inscription', $request->id_inscription)->where('id_frais_scolarite', $request->id_frais)->first();
            }
            
            // Mettre à jour le statut de la facture si tout est payé
            $totalVerseGlobal = $payeTotal + $request->montant;
            if ($totalVerseGlobal >= $net && $factureExistante) {
                $factureExistante->statut_facture = 'Payée';
                $factureExistante->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Paiement enregistré avec succès.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur: '.$e->getMessage()], 500);
        }
    }

    public function storeFacture(Request $request)
    {
        $request->validate([
            'numero_facture' => 'required|string',
            'id_inscription' => 'required|integer',
            'id_frais' => 'required|integer',
            'date' => 'required|date',
            'statut' => 'required|string'
        ]);

        $etabId = session('etablissement_id', 1);

        $echeancier = EcheancierScolarite::where('id_inscription', $request->id_inscription)
                            ->where('id_frais_scolarite', $request->id_frais)
                            ->first();
        $net = $echeancier ? max(0, $echeancier->montant_total - $echeancier->montant_remise) : 0;

        Facture::create([
            'numero_facture' => $request->numero_facture,
            'id_inscription' => $request->id_inscription,
            'id_frais_scolarite' => $request->id_frais,
            'montant_total' => $net,
            'date_facture' => $request->date,
            'statut_facture' => $request->statut,
            'id_etablissement' => $etabId
        ]);

        return response()->json(['success' => true, 'message' => 'Facture enregistrée.']);
    }
}
