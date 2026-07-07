<?php

namespace App\Traits;

use App\Models\AnneeScolaire;
use App\Models\CategorieComptable;
use App\Models\CompteComptable;
use App\Models\EcritureComptable;
use App\Models\FraisScolarite;
use App\Models\Inscription;
use App\Models\JournalComptable;
use App\Models\LigneEcriture;
use App\Models\ModePaiement;
use App\Models\OperationComptable;
use App\Models\Paiement;
use App\Models\ParametrageComptable;

/**
 * Port PHP du service Flutter ComptabiliteEngine (moteur comptable SYSCOHADA).
 * Partagé entre TresorerieController et StocksController.
 */
trait ComptabiliteEngineTrait
{
    private array $comptesDefaut = [
        ['numero' => '1012', 'libelle' => 'Capital personnel', 'classe' => 1, 'type' => 'Capitaux', 'sens' => 'Crédit'],
        ['numero' => '1621', 'libelle' => 'Emprunts bancaires', 'classe' => 1, 'type' => 'Passif', 'sens' => 'Crédit'],
        ['numero' => '4111', 'libelle' => 'Étudiants (clients)', 'classe' => 4, 'type' => 'Actif', 'sens' => 'Débit'],
        ['numero' => '4011', 'libelle' => 'Fournisseurs', 'classe' => 4, 'type' => 'Passif', 'sens' => 'Crédit'],
        ['numero' => '5711', 'libelle' => 'Caisse', 'classe' => 5, 'type' => 'Trésorerie', 'sens' => 'Débit'],
        ['numero' => '5211', 'libelle' => 'Banque', 'classe' => 5, 'type' => 'Trésorerie', 'sens' => 'Débit'],
        ['numero' => '5311', 'libelle' => 'Mobile Money', 'classe' => 5, 'type' => 'Trésorerie', 'sens' => 'Débit'],
        ['numero' => '6041', 'libelle' => 'Achats fournitures scolaires', 'classe' => 6, 'type' => 'Charge', 'sens' => 'Débit'],
        ['numero' => '6222', 'libelle' => 'Loyer (locations bâtiments)', 'classe' => 6, 'type' => 'Charge', 'sens' => 'Débit'],
        ['numero' => '6111', 'libelle' => 'Transports sur achats', 'classe' => 6, 'type' => 'Charge', 'sens' => 'Débit'],
        ['numero' => '6611', 'libelle' => 'Salaires et traitements', 'classe' => 6, 'type' => 'Charge', 'sens' => 'Débit'],
        ['numero' => '6641', 'libelle' => 'Charges sociales', 'classe' => 6, 'type' => 'Charge', 'sens' => 'Débit'],
        ['numero' => '6581', 'libelle' => 'Charges diverses', 'classe' => 6, 'type' => 'Charge', 'sens' => 'Débit'],
        ['numero' => '7061', 'libelle' => 'Frais de scolarité', 'classe' => 7, 'type' => 'Produit', 'sens' => 'Crédit'],
        ['numero' => '7062', 'libelle' => "Droits d'inscription", 'classe' => 7, 'type' => 'Produit', 'sens' => 'Crédit'],
        ['numero' => '7063', 'libelle' => 'Cantine', 'classe' => 7, 'type' => 'Produit', 'sens' => 'Crédit'],
        ['numero' => '7064', 'libelle' => 'Transport scolaire', 'classe' => 7, 'type' => 'Produit', 'sens' => 'Crédit'],
        ['numero' => '7011', 'libelle' => 'Ventes de marchandises', 'classe' => 7, 'type' => 'Produit', 'sens' => 'Crédit'],
        ['numero' => '7581', 'libelle' => 'Autres produits', 'classe' => 7, 'type' => 'Produit', 'sens' => 'Crédit'],
    ];

    private array $journauxDefaut = [
        ['code' => 'VT', 'libelle' => 'Journal des Ventes'],
        ['code' => 'AC', 'libelle' => 'Journal des Achats'],
        ['code' => 'BQ', 'libelle' => 'Journal Banque'],
        ['code' => 'CA', 'libelle' => 'Journal Caisse'],
        ['code' => 'OD', 'libelle' => 'Journal Opérations Diverses'],
    ];

    public const TYPES_OPERATIONS = [
        ['code' => 'paiement_caisse', 'libelle' => 'Paiement scolarité (caisse / espèces)'],
        ['code' => 'paiement_banque', 'libelle' => 'Paiement scolarité (banque / mobile money)'],
        ['code' => 'frais_inscription', 'libelle' => "Frais d'inscription"],
        ['code' => 'stock_vente', 'libelle' => 'Vente article / stock'],
        ['code' => 'depense_caisse', 'libelle' => 'Dépense (caisse)'],
        ['code' => 'depense_banque', 'libelle' => 'Dépense (banque / virement)'],
        ['code' => 'remboursement', 'libelle' => 'Remboursement étudiant'],
        ['code' => 'bourse_remise', 'libelle' => 'Bourse / remise accordée'],
    ];

    private function initialiserDefautsInterne($etabId)
    {
        foreach ($this->journauxDefaut as $j) {
            $existe = JournalComptable::where('id_etablissement', $etabId)->where('code', $j['code'])->exists();
            if (!$existe) {
                JournalComptable::create(['id_etablissement' => $etabId, 'code' => $j['code'], 'libelle' => $j['libelle']]);
            }
        }
        foreach ($this->comptesDefaut as $c) {
            $existe = CompteComptable::where('id_etablissement', $etabId)->where('numero_compte', $c['numero'])->exists();
            if (!$existe) {
                CompteComptable::create([
                    'id_etablissement' => $etabId, 'numero_compte' => $c['numero'], 'libelle' => $c['libelle'],
                    'classe' => $c['classe'], 'type_compte' => $c['type'], 'sens_normal' => $c['sens'], 'actif' => true,
                ]);
            }
        }
    }

    private function creerEcritureInterne($etabId, $typeOperation, $libelle, $montant, $date, $origine = null, $idOrigine = null, $numeroPiece = null)
    {
        if ($montant <= 0) {
            return;
        }
        $param = ParametrageComptable::where('id_etablissement', $etabId)->where('type_operation', $typeOperation)->first();
        if (!$param || !$param->id_journal || !$param->id_compte_debit || !$param->id_compte_credit) {
            return;
        }

        $journal = JournalComptable::find($param->id_journal);
        $codeJ   = $journal?->code ?? 'OD';
        $anneeActive = AnneeScolaire::where('id_etablissement', $etabId)->where('active', true)->first();

        $year   = date('Y');
        $prefix = "$codeJ-$year-";
        $maxN   = EcritureComptable::where('id_etablissement', $etabId)
            ->where('numero_piece', 'like', $prefix.'%')
            ->get()
            ->map(fn ($e) => (int) substr($e->numero_piece, strlen($prefix)))
            ->max() ?? 0;
        $piece = $numeroPiece ?: $prefix.str_pad($maxN + 1, 5, '0', STR_PAD_LEFT);

        $ecriture = EcritureComptable::create([
            'id_etablissement'   => $etabId,
            'id_journal'         => $param->id_journal,
            'id_annee_scolaire'  => $anneeActive?->id,
            'numero_piece'       => $piece,
            'date_ecriture'      => $date,
            'libelle'            => $libelle,
            'origine'            => $origine,
            'id_origine'         => $idOrigine,
            'valide'             => false,
            'total_debit'        => $montant,
            'total_credit'       => $montant,
        ]);

        LigneEcriture::create([
            'id_etablissement' => $etabId, 'id_ecriture' => $ecriture->id, 'id_compte' => $param->id_compte_debit,
            'sens' => 'Débit', 'montant' => $montant, 'libelle_ligne' => $libelle,
        ]);
        LigneEcriture::create([
            'id_etablissement' => $etabId, 'id_ecriture' => $ecriture->id, 'id_compte' => $param->id_compte_credit,
            'sens' => 'Crédit', 'montant' => $montant, 'libelle_ligne' => $libelle,
        ]);
    }

    private function onPaiementInterne($etabId, Paiement $p)
    {
        $montant = (float) $p->montant_verse;
        if ($montant <= 0) {
            return;
        }
        $mode    = $p->id_mode_paiement ? ModePaiement::find($p->id_mode_paiement) : null;
        $modeLib = strtolower($mode?->libelle ?? '');
        $isBanque = str_contains($modeLib, 'banque') || str_contains($modeLib, 'mobile') || str_contains($modeLib, 'orange')
            || str_contains($modeLib, 'mtn') || str_contains($modeLib, 'wave') || str_contains($modeLib, 'virement');

        $ins = Inscription::with('etudiant')->find($p->id_inscription);
        $etu = $ins?->etudiant;
        $nom = $etu ? "{$etu->nom} {$etu->prenom}" : '';

        $this->creerEcritureInterne(
            $etabId,
            $isBanque ? 'paiement_banque' : 'paiement_caisse',
            'Paiement scolarité'.($nom ? " — $nom" : ''),
            $montant,
            $p->date ? substr((string) $p->date, 0, 10) : now()->toDateString(),
            'paiement',
            $p->id,
            $p->reference
        );
    }

    private function onInscriptionInterne($etabId, Inscription $ins)
    {
        $etu = $ins->etudiant;

        $fraisIns = FraisScolarite::with('typeFrais')->where('id_etablissement', $etabId)
            ->where('id_niveau', $ins->id_niveau)
            ->get()
            ->first(fn ($f) => str_contains(strtolower($f->typeFrais?->libelle ?? ''), 'inscri'));

        if (!$fraisIns || (float) $fraisIns->montant <= 0) {
            return;
        }

        $this->creerEcritureInterne(
            $etabId,
            'frais_inscription',
            "Frais d'inscription".($etu ? " — {$etu->nom} {$etu->prenom}" : ''),
            (float) $fraisIns->montant,
            $ins->date_inscription ?: now()->toDateString(),
            'inscription',
            $ins->id,
            $ins->numero_inscription
        );
    }

    private function onDepenseInterne($etabId, $montant, $libelle, $isBanque = false, $idOrigine = null, $date = null)
    {
        $this->creerEcritureInterne(
            $etabId,
            $isBanque ? 'depense_banque' : 'depense_caisse',
            $libelle,
            $montant,
            $date ?: now()->toDateString(),
            'depense',
            $idOrigine
        );
    }

    private function onStockAchatInterne($etabId, $montant, $libelle, $isBanque = false, $idOrigine = null)
    {
        $this->creerEcritureInterne(
            $etabId,
            $isBanque ? 'depense_banque' : 'depense_caisse',
            $libelle,
            $montant,
            now()->toDateString(),
            'stock',
            $idOrigine
        );
    }

    private function onStockVenteInterne($etabId, $montant, $libelle, $idOrigine = null)
    {
        $this->creerEcritureInterne(
            $etabId,
            'stock_vente',
            $libelle,
            $montant,
            now()->toDateString(),
            'stock',
            $idOrigine
        );
    }

    /**
     * Port de appState.createStockOperation : crée une ligne dans operation_comptable
     * (auto-crée la catégorie comptable si elle n'existe pas encore), sans passer par
     * le moteur d'écritures en partie double.
     */
    private function createStockOperationInterne($etabId, $typeOperation, $libelle, $montant, $categorieLibelle)
    {
        $cat = CategorieComptable::where('id_etablissement', $etabId)
            ->whereRaw('LOWER(libelle) = ?', [strtolower($categorieLibelle)])
            ->first();

        if (!$cat) {
            $cat = CategorieComptable::create([
                'code'             => $typeOperation === 'Entrée' ? 'VART' : 'AART',
                'libelle'          => $categorieLibelle,
                'type_categorie'   => $typeOperation === 'Entrée' ? 'Recette' : 'Dépense',
                'id_statut'        => 1,
                'id_etablissement' => $etabId,
            ]);
        }

        $anneeActive = AnneeScolaire::where('id_etablissement', $etabId)->where('active', true)->first();

        OperationComptable::create([
            'libelle'                => $libelle,
            'montant'                => $montant,
            'type_operation'         => $typeOperation,
            'id_categorie_comptable' => $cat->id,
            'date'                   => now()->toDateString(),
            'origine'                => 'stock',
            'id_annee_scolaire'      => $anneeActive?->id,
            'id_statut'              => 1,
            'id_etablissement'       => $etabId,
        ]);
    }
}
