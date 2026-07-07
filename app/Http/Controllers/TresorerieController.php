<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\CategorieComptable;
use App\Models\CompteComptable;
use App\Models\EcritureComptable;
use App\Models\Enseignant;
use App\Models\Etudiant;
use App\Models\FraisScolarite;
use App\Models\Inscription;
use App\Models\JournalComptable;
use App\Models\LigneEcriture;
use App\Models\ModePaiement;
use App\Models\OperationComptable;
use App\Models\Paiement;
use App\Models\ParametrageComptable;
use App\Models\SalaireEnseignant;
use App\Models\TypeFrais;
use App\Traits\ComptabiliteEngineTrait;
use Illuminate\Http\Request;

class TresorerieController extends Controller
{
    use ComptabiliteEngineTrait;

    private function etabId()
    {
        return session('etablissement_id');
    }

    private function anneeActive()
    {
        return AnneeScolaire::where('id_etablissement', $this->etabId())->where('active', true)->first();
    }

    // ══ OPÉRATIONS (Trésorerie — Flux Financiers) ════════════════════════════════

    public function operations()
    {
        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();

        $operations = OperationComptable::with('categorie')
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();

        $categories = CategorieComptable::where('id_etablissement', $etabId)->orderBy('libelle')->get();

        $paiements = Paiement::with(['inscription.etudiant'])
            ->whereHas('inscription', fn ($q) => $q->where('id_etablissement', $etabId)
                ->when($anneeActive, fn ($q2) => $q2->where('id_annee_scolaire', $anneeActive->id)))
            ->get();

        $salaires = SalaireEnseignant::with('enseignant')
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();

        return view('tresorerie.operations', compact('operations', 'categories', 'paiements', 'salaires', 'anneeActive'));
    }

    public function storeOperation(Request $r)
    {
        $r->validate([
            'libelle'        => 'required|string',
            'montant'        => 'required|numeric|min:0',
            'type_operation' => 'required|in:Entrée,Sortie',
            'date'           => 'required|date',
            'id_categorie_comptable' => 'nullable|integer',
        ]);
        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();

        $op = OperationComptable::create([
            'libelle'                => $r->libelle,
            'montant'                => $r->montant,
            'type_operation'         => $r->type_operation,
            'date'                   => $r->date,
            'id_categorie_comptable' => $r->id_categorie_comptable ?: null,
            'id_annee_scolaire'      => $anneeActive?->id,
            'id_statut'              => 1,
            'id_etablissement'       => $etabId,
        ]);

        if ($r->type_operation === 'Sortie') {
            $this->onDepenseInterne($etabId, (float) $r->montant, $r->libelle, false, $op->id, $r->date);
        }

        return response()->json(['message' => 'Opération enregistrée.', 'data' => $op]);
    }

    public function updateOperation(Request $r, $id)
    {
        $r->validate([
            'libelle'        => 'required|string',
            'montant'        => 'required|numeric|min:0',
            'type_operation' => 'required|in:Entrée,Sortie',
            'date'           => 'required|date',
            'id_categorie_comptable' => 'nullable|integer',
        ]);
        $op = OperationComptable::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $op->update([
            'libelle'                => $r->libelle,
            'montant'                => $r->montant,
            'type_operation'         => $r->type_operation,
            'date'                   => $r->date,
            'id_categorie_comptable' => $r->id_categorie_comptable ?: null,
        ]);

        return response()->json(['message' => 'Opération modifiée.', 'data' => $op]);
    }

    public function destroyOperation($id)
    {
        OperationComptable::where('id', $id)->where('id_etablissement', $this->etabId())->delete();

        return response()->json(['message' => 'Opération supprimée.']);
    }

    public function initPlanComptable()
    {
        $this->initialiserDefautsInterne($this->etabId());

        return response()->json(['message' => 'Plan comptable et journaux SYSCOHADA initialisés avec succès !']);
    }

    public function genererEcritures()
    {
        $etabId = $this->etabId();
        $count  = 0;

        $idsTraites = EcritureComptable::where('id_etablissement', $etabId)
            ->where('origine', 'paiement')->whereNotNull('id_origine')->pluck('id_origine')->map(fn ($v) => (string) $v)->all();

        $paiements = Paiement::whereHas('inscription', fn ($q) => $q->where('id_etablissement', $etabId))->get();
        foreach ($paiements as $p) {
            if (in_array((string) $p->id, $idsTraites)) {
                continue;
            }
            if ((float) $p->montant_verse <= 0) {
                continue;
            }
            $this->onPaiementInterne($etabId, $p);
            $count++;
        }

        $idsInsTraites = EcritureComptable::where('id_etablissement', $etabId)
            ->where('origine', 'inscription')->whereNotNull('id_origine')->pluck('id_origine')->map(fn ($v) => (string) $v)->all();

        $inscriptions = Inscription::with('etudiant')->where('id_etablissement', $etabId)->get();
        foreach ($inscriptions as $ins) {
            if (in_array((string) $ins->id, $idsInsTraites)) {
                continue;
            }
            $this->onInscriptionInterne($etabId, $ins);
            $count++;
        }

        return response()->json(['count' => $count]);
    }

    // ══ CATÉGORIES & BILAN ═══════════════════════════════════════════════════════

    public function categoriesBilan()
    {
        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();

        $categories = CategorieComptable::where('id_etablissement', $etabId)->orderBy('libelle')->get();

        $operations = OperationComptable::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();

        $paiements = Paiement::with(['inscription.etudiant'])
            ->whereHas('inscription', fn ($q) => $q->where('id_etablissement', $etabId)
                ->when($anneeActive, fn ($q2) => $q2->where('id_annee_scolaire', $anneeActive->id)))
            ->get();

        $salaires = SalaireEnseignant::with('enseignant')
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();

        return view('tresorerie.categories-bilan', compact('categories', 'operations', 'paiements', 'salaires', 'anneeActive'));
    }

    public function storeCategorie(Request $r)
    {
        $r->validate(['code' => 'required|string', 'libelle' => 'required|string', 'type_categorie' => 'required|in:Recette,Dépense']);
        $cat = CategorieComptable::create([
            'code' => $r->code, 'libelle' => $r->libelle, 'type_categorie' => $r->type_categorie,
            'id_statut' => 1, 'id_etablissement' => $this->etabId(),
        ]);

        return response()->json(['message' => 'Catégorie créée.', 'data' => $cat]);
    }

    public function updateCategorie(Request $r, $id)
    {
        $r->validate(['code' => 'required|string', 'libelle' => 'required|string', 'type_categorie' => 'required|in:Recette,Dépense']);
        $cat = CategorieComptable::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $cat->update(['code' => $r->code, 'libelle' => $r->libelle, 'type_categorie' => $r->type_categorie]);

        return response()->json(['message' => 'Catégorie modifiée.', 'data' => $cat]);
    }

    public function destroyCategorie($id)
    {
        CategorieComptable::where('id', $id)->where('id_etablissement', $this->etabId())->delete();

        return response()->json(['message' => 'Catégorie supprimée.']);
    }

    // ══ PLAN COMPTABLE ════════════════════════════════════════════════════════════

    public function planComptable()
    {
        $etabId  = $this->etabId();
        $comptes = CompteComptable::where('id_etablissement', $etabId)->orderBy('numero_compte')->get();

        return view('tresorerie.plan-comptable', compact('comptes'));
    }

    public function storeCompte(Request $r)
    {
        $r->validate([
            'numero_compte' => 'required|string', 'libelle' => 'required|string', 'classe' => 'required|integer|min:1|max:8',
            'type_compte' => 'required|string', 'sens_normal' => 'required|in:Débit,Crédit',
        ]);
        $compte = CompteComptable::create([
            'numero_compte' => $r->numero_compte, 'libelle' => $r->libelle, 'classe' => $r->classe,
            'type_compte' => $r->type_compte, 'sens_normal' => $r->sens_normal, 'actif' => $r->boolean('actif', true),
            'id_etablissement' => $this->etabId(),
        ]);

        return response()->json(['message' => 'Compte créé.', 'data' => $compte]);
    }

    public function updateCompte(Request $r, $id)
    {
        $r->validate([
            'numero_compte' => 'required|string', 'libelle' => 'required|string', 'classe' => 'required|integer|min:1|max:8',
            'type_compte' => 'required|string', 'sens_normal' => 'required|in:Débit,Crédit',
        ]);
        $compte = CompteComptable::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $compte->update([
            'numero_compte' => $r->numero_compte, 'libelle' => $r->libelle, 'classe' => $r->classe,
            'type_compte' => $r->type_compte, 'sens_normal' => $r->sens_normal, 'actif' => $r->boolean('actif', true),
        ]);

        return response()->json(['message' => 'Compte modifié.', 'data' => $compte]);
    }

    public function destroyCompte($id)
    {
        CompteComptable::where('id', $id)->where('id_etablissement', $this->etabId())->delete();

        return response()->json(['message' => 'Compte supprimé.']);
    }

    // ══ PARAMÉTRAGE COMPTABLE ═════════════════════════════════════════════════════

    public function parametrage()
    {
        $etabId       = $this->etabId();
        $parametrages = ParametrageComptable::where('id_etablissement', $etabId)->get();
        $comptes      = CompteComptable::where('id_etablissement', $etabId)->where('actif', true)->orderBy('numero_compte')->get();
        $journaux     = JournalComptable::where('id_etablissement', $etabId)->orderBy('code')->get();

        return view('tresorerie.parametrage', compact('parametrages', 'comptes', 'journaux'));
    }

    public function storeParametrage(Request $r)
    {
        $r->validate([
            'type_operation'    => 'required|string',
            'id_compte_debit'   => 'required|integer',
            'id_compte_credit'  => 'required|integer',
            'id_journal'        => 'nullable|integer',
        ]);
        $etabId   = $this->etabId();
        $existing = ParametrageComptable::where('id_etablissement', $etabId)->where('type_operation', $r->type_operation)->first();

        $payload = [
            'id_compte_debit'  => $r->id_compte_debit,
            'id_compte_credit' => $r->id_compte_credit,
            'id_journal'       => $r->id_journal ?: null,
        ];

        if ($existing) {
            $existing->update($payload);
            $param = $existing;
        } else {
            $param = ParametrageComptable::create(array_merge($payload, [
                'type_operation'   => $r->type_operation,
                'id_etablissement' => $etabId,
            ]));
        }

        return response()->json(['message' => 'Paramétrage enregistré.', 'data' => $param]);
    }

    public function appliquerDefautsParametrage()
    {
        $etabId = $this->etabId();

        $defauts = [
            ['type' => 'paiement_caisse', 'debit' => '5711', 'credit' => '4111', 'journal' => 'CA'],
            ['type' => 'paiement_banque', 'debit' => '5211', 'credit' => '4111', 'journal' => 'BQ'],
            ['type' => 'frais_inscription', 'debit' => '5711', 'credit' => '7062', 'journal' => 'CA'],
            ['type' => 'stock_vente', 'debit' => '5711', 'credit' => '7011', 'journal' => 'CA'],
            ['type' => 'depense_caisse', 'debit' => '6581', 'credit' => '5711', 'journal' => 'CA'],
            ['type' => 'depense_banque', 'debit' => '6581', 'credit' => '5211', 'journal' => 'BQ'],
            ['type' => 'remboursement', 'debit' => '4111', 'credit' => '5711', 'journal' => 'CA'],
            ['type' => 'bourse_remise', 'debit' => '7061', 'credit' => '4111', 'journal' => 'OD'],
        ];

        $comptes  = CompteComptable::where('id_etablissement', $etabId)->get()->keyBy('numero_compte');
        $journaux = JournalComptable::where('id_etablissement', $etabId)->get()->keyBy('code');

        foreach ($defauts as $d) {
            $idD = $comptes->get($d['debit'])?->id;
            $idC = $comptes->get($d['credit'])?->id;
            $idJ = $journaux->get($d['journal'])?->id;
            if (!$idD || !$idC || !$idJ) {
                continue;
            }
            $existing = ParametrageComptable::where('id_etablissement', $etabId)->where('type_operation', $d['type'])->first();
            $payload  = ['id_compte_debit' => $idD, 'id_compte_credit' => $idC, 'id_journal' => $idJ];
            if ($existing) {
                $existing->update($payload);
            } else {
                ParametrageComptable::create(array_merge($payload, ['type_operation' => $d['type'], 'id_etablissement' => $etabId]));
            }
        }

        return response()->json(['message' => 'Paramétrage SYSCOHADA par défaut appliqué.']);
    }

    // ══ JOURNAUX / GRAND LIVRE / EXPORT SAGE (lecture seule) ══════════════════════

    private function ecrituresDataPourEtab($etabId)
    {
        $ecritures = EcritureComptable::where('id_etablissement', $etabId)->orderByDesc('date_ecriture')->get();
        $lignes    = LigneEcriture::where('id_etablissement', $etabId)->get();
        $comptes   = CompteComptable::where('id_etablissement', $etabId)->orderBy('numero_compte')->get();
        $journaux  = JournalComptable::where('id_etablissement', $etabId)->orderBy('code')->get();

        return compact('ecritures', 'lignes', 'comptes', 'journaux');
    }

    public function journaux()
    {
        return view('tresorerie.journaux', $this->ecrituresDataPourEtab($this->etabId()));
    }

    public function grandLivre()
    {
        return view('tresorerie.grand-livre', $this->ecrituresDataPourEtab($this->etabId()));
    }

    public function exportSage()
    {
        return view('tresorerie.export-sage', $this->ecrituresDataPourEtab($this->etabId()));
    }
}
