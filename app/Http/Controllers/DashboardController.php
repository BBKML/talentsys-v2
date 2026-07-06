<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $etabId = session('etablissement_id');

        $stats = ['etudiants' => 0, 'enseignants' => 0, 'chiffre_affaire' => 0, 'inscriptions' => 0];
        $statsRapides = ['classes' => 0, 'filieres' => 0, 'matieres' => 0, 'masse_salariale' => 0];
        $chartNiveaux    = collect();
        $chartFilieres   = collect();
        $activiteRecente = collect();
        $analyseNiveaux  = collect();
        $analyseFilieres = collect();
        $anneeActive     = null;

        $situationActuelle = ['effectifs' => 0, 'revenus' => 0.0, 'taux' => 0.0];
        $situationOptimale = ['effectifs' => 0, 'revenus' => 0.0, 'taux' => 100.0];
        $ecarts            = ['places_vacantes' => 0, 'perte' => 0.0, 'classes_sous_utilisees' => 0];

        if (!$etabId) {
            return view('dashboard', compact(
                'stats', 'statsRapides', 'chartNiveaux', 'chartFilieres',
                'activiteRecente', 'analyseNiveaux', 'analyseFilieres',
                'situationActuelle', 'situationOptimale', 'ecarts', 'anneeActive'
            ));
        }

        // ── Année active ──────────────────────────────────────────────────
        try {
            $anneeActive = DB::table('annee_scolaire')
                ->where('id_etablissement', $etabId)
                ->where('active', true)
                ->first();
        } catch (\Throwable $e) { Log::warning('annee_scolaire: '.$e->getMessage()); }

        // ── Stats principales ─────────────────────────────────────────────
        try { $stats['etudiants']  = DB::table('etudiant')->where('id_etablissement', $etabId)->count(); } catch (\Throwable $e) {}
        try { $stats['enseignants'] = DB::table('enseignant')->where('id_etablissement', $etabId)->count(); } catch (\Throwable $e) {}

        if ($anneeActive) {
            try {
                $stats['inscriptions'] = DB::table('inscription')
                    ->where('id_etablissement', $etabId)
                    ->where('id_annee_scolaire', $anneeActive->id)
                    ->count();
            } catch (\Throwable $e) {}

            // Chiffre d'affaire = montant_verse réel (via inscription → établissement/année)
            // Flutter : paiements.where(p => inscsIds.contains(p['id_inscription'])).sum(montant_verse)
            try {
                $stats['chiffre_affaire'] = (float) DB::table('historique_paiement as hp')
                    ->join('inscription as i', 'i.id', '=', 'hp.id_inscription')
                    ->where('i.id_etablissement', $etabId)
                    ->where('i.id_annee_scolaire', $anneeActive->id)
                    ->sum('hp.montant_verse');
            } catch (\Throwable $e) { Log::warning('chiffre_affaire: '.$e->getMessage()); }
        }

        // ── Stats rapides ─────────────────────────────────────────────────
        try { $statsRapides['classes']  = DB::table('classe')->where('id_etablissement', $etabId)->count(); } catch (\Throwable $e) {}
        try { $statsRapides['filieres'] = DB::table('filiere')->where('id_etablissement', $etabId)->count(); } catch (\Throwable $e) {}
        try { $statsRapides['matieres'] = DB::table('matiere')->where('id_etablissement', $etabId)->count(); } catch (\Throwable $e) {}
        try { $statsRapides['masse_salariale'] = (float) DB::table('salaire_enseignant')->where('id_etablissement', $etabId)->sum('montant'); } catch (\Throwable $e) {}

        // ── Graphiques ────────────────────────────────────────────────────
        if ($anneeActive) {
            try {
                // inscription.id_niveau direct (pas besoin de joindre classe)
                $chartNiveaux = DB::table('inscription as i')
                    ->join('niveau as n', 'n.id', '=', 'i.id_niveau')
                    ->where('i.id_etablissement', $etabId)
                    ->where('i.id_annee_scolaire', $anneeActive->id)
                    ->groupBy('n.libelle')
                    ->selectRaw('n.libelle, count(*) as total')
                    ->orderBy('n.libelle')
                    ->get();
            } catch (\Throwable $e) { Log::warning('chartNiveaux: '.$e->getMessage()); }

            try {
                // inscription.id_filiere direct
                $chartFilieres = DB::table('inscription as i')
                    ->join('filiere as f', 'f.id', '=', 'i.id_filiere')
                    ->where('i.id_etablissement', $etabId)
                    ->where('i.id_annee_scolaire', $anneeActive->id)
                    ->groupBy('f.libelle')
                    ->selectRaw('f.libelle, count(*) as total')
                    ->get();
            } catch (\Throwable $e) { Log::warning('chartFilieres: '.$e->getMessage()); }
        }

        // ── Activité récente ──────────────────────────────────────────────
        // historique_paiement → inscription → etudiant
        try {
            $paiements = DB::table('historique_paiement as hp')
                ->join('inscription as i', 'i.id', '=', 'hp.id_inscription')
                ->leftJoin('etudiant as e', 'e.id', '=', 'i.id_etudiant')
                ->where('i.id_etablissement', $etabId)
                ->orderBy('hp.created_at', 'desc')
                ->limit(5)
                ->selectRaw("
                    'paiement' as type,
                    hp.montant_verse as montant,
                    COALESCE(e.prenom||' '||e.nom, 'Inconnu') as nom,
                    hp.date as date
                ")
                ->get();
        } catch (\Throwable $e) {
            Log::warning('activite paiements: '.$e->getMessage());
            $paiements = collect();
        }

        try {
            $inscrs = DB::table('inscription as i')
                ->leftJoin('etudiant as e', 'e.id', '=', 'i.id_etudiant')
                ->where('i.id_etablissement', $etabId)
                ->orderBy('i.created_at', 'desc')
                ->limit(5)
                ->selectRaw("
                    'inscription' as type,
                    NULL::numeric as montant,
                    COALESCE(e.prenom||' '||e.nom, 'Inconnu') as nom,
                    i.date_inscription as date
                ")
                ->get();
        } catch (\Throwable $e) {
            Log::warning('activite inscrs: '.$e->getMessage());
            $inscrs = collect();
        }

        $activiteRecente = $paiements->concat($inscrs)->sortByDesc('date')->take(6)->values();

        // ── Analyse financière ─────────────────────────────────────────────
        // Logique identique au Flutter :
        //   recette    = inscrits × frais_scolarite.montant  (pas historique_paiement)
        //   rev_max    = capacite_max × frais_scolarite.montant
        //   manque     = rev_max - recette
        // ─────────────────────────────────────────────────────────────────
        if ($anneeActive) {
            try {
                $totalInscritsA  = 0;
                $totalRecetteA   = 0.0;
                $totalCapaciteA  = 0;
                $totalRevMaxA    = 0.0;

                // Niveaux avec capacite_max (colonne correcte)
                $niveaux = DB::table('niveau as n')
                    ->join('classe as c', 'c.id_niveau', '=', 'n.id')
                    ->where('c.id_etablissement', $etabId)
                    ->groupBy('n.id', 'n.libelle')
                    ->selectRaw('n.id, n.libelle, COALESCE(SUM(c.capacite_max),0)::int as capacite')
                    ->orderBy('n.libelle')
                    ->get();

                foreach ($niveaux as $niv) {
                    // Inscriptions pour ce niveau (colonne directe sur inscription)
                    $inscrits = DB::table('inscription')
                        ->where('id_etablissement', $etabId)
                        ->where('id_annee_scolaire', $anneeActive->id)
                        ->where('id_niveau', $niv->id)
                        ->count();

                    // Frais scolarité pour ce niveau + cette année
                    $frais = 0.0;
                    try {
                        $frais = (float) DB::table('frais_scolarite')
                            ->where('id_niveau', $niv->id)
                            ->where('id_annee_scolaire', $anneeActive->id)
                            ->sum('montant');
                    } catch (\Throwable $e) { Log::warning('frais_scolarite: '.$e->getMessage()); }

                    $capacite = (int)$niv->capacite;
                    $recette  = $inscrits * $frais;
                    $revMax   = $capacite * $frais;
                    $taux     = $capacite > 0 ? round($inscrits / $capacite * 100, 1) : 0.0;

                    $totalInscritsA  += $inscrits;
                    $totalRecetteA   += $recette;
                    $totalCapaciteA  += $capacite;
                    $totalRevMaxA    += $revMax;

                    $analyseNiveaux->push((object)[
                        'libelle'  => $niv->libelle,
                        'inscrits' => $inscrits,
                        'capacite' => $capacite,
                        'recette'  => $recette,
                        'rev_max'  => $revMax,
                        'manque'   => max(0.0, $revMax - $recette),
                        'taux'     => $taux,
                    ]);
                }

                // Filières
                $filieres = DB::table('filiere as f')
                    ->join('classe as c', 'c.id_filiere', '=', 'f.id')
                    ->where('c.id_etablissement', $etabId)
                    ->groupBy('f.id', 'f.libelle')
                    ->selectRaw('f.id, f.libelle, COALESCE(SUM(c.capacite_max),0)::int as capacite')
                    ->get();

                foreach ($filieres as $fil) {
                    $inscrits = DB::table('inscription')
                        ->where('id_etablissement', $etabId)
                        ->where('id_annee_scolaire', $anneeActive->id)
                        ->where('id_filiere', $fil->id)
                        ->count();

                    // Frais par filière : on cherche dans frais_scolarite par id_filiere
                    $frais = 0.0;
                    try {
                        $frais = (float) DB::table('frais_scolarite')
                            ->where('id_filiere', $fil->id)
                            ->where('id_annee_scolaire', $anneeActive->id)
                            ->sum('montant');
                    } catch (\Throwable $e) {}

                    // Fallback : si les frais sont définis par niveau, on recalcule
                    // depuis les niveaux déjà traités qui appartiennent à cette filière
                    if ($frais == 0 && $totalInscritsA > 0) {
                        // Proportion : on utilise les totaux déjà calculés
                        $recette = $totalRecetteA;
                        $revMax  = $totalRevMaxA;
                    } else {
                        $capacite = (int)$fil->capacite;
                        $recette  = $inscrits * $frais;
                        $revMax   = $capacite * $frais;
                    }

                    $capacite = (int)$fil->capacite;
                    $taux     = $capacite > 0 ? round($inscrits / $capacite * 100, 1) : 0.0;

                    $analyseFilieres->push((object)[
                        'libelle'  => $fil->libelle,
                        'inscrits' => $inscrits,
                        'capacite' => $capacite,
                        'recette'  => $recette,
                        'rev_max'  => $revMax,
                        'manque'   => max(0.0, $revMax - $recette),
                        'taux'     => $taux,
                    ]);
                }

                // Classes sous-utilisées (<50%) — utilise capacite_max
                $classesSousUtil = 0;
                try {
                    $classesSousUtil = DB::table('classe as c')
                        ->leftJoin(DB::raw(
                            "(SELECT id_classe, COUNT(*) as nb FROM inscription
                              WHERE id_etablissement = $etabId
                              AND id_annee_scolaire = {$anneeActive->id}
                              GROUP BY id_classe) as ins"
                        ), 'ins.id_classe', '=', 'c.id')
                        ->where('c.id_etablissement', $etabId)
                        ->whereRaw('(COALESCE(ins.nb,0)::float / NULLIF(c.capacite_max,0)) < 0.5')
                        ->count();
                } catch (\Throwable $e) {}

                $tauxGlobal = $totalCapaciteA > 0
                    ? round($totalInscritsA / $totalCapaciteA * 100, 1)
                    : 0.0;

                $situationActuelle = [
                    'effectifs' => $totalInscritsA,
                    'revenus'   => $totalRecetteA,
                    'taux'      => $tauxGlobal,
                ];
                $situationOptimale = [
                    'effectifs' => $totalCapaciteA,
                    'revenus'   => $totalRevMaxA,
                    'taux'      => 100.0,
                ];
                $ecarts = [
                    'places_vacantes'        => max(0, $totalCapaciteA - $totalInscritsA),
                    'perte'                  => max(0.0, $totalRevMaxA - $totalRecetteA),
                    'classes_sous_utilisees' => $classesSousUtil,
                ];

            } catch (\Throwable $e) {
                Log::error('Dashboard analyse financière: '.$e->getMessage());
            }
        }

        return view('dashboard', compact(
            'stats', 'statsRapides', 'chartNiveaux', 'chartFilieres',
            'activiteRecente', 'analyseNiveaux', 'analyseFilieres',
            'situationActuelle', 'situationOptimale', 'ecarts', 'anneeActive'
        ));
    }
}
