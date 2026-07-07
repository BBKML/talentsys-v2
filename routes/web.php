<?php

use App\Http\Controllers\Auth\EtablissementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UtilisateursController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\EtablissementInfoController;
use App\Http\Controllers\SallesController;
use App\Http\Controllers\AcademiqueController;
use App\Http\Controllers\ScolariteController;
use App\Http\Controllers\EnseignantsController;
use App\Http\Controllers\EvaluationsController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\TresorerieController;
use App\Http\Controllers\StocksController;
use App\Http\Controllers\AchatsController;
use App\Http\Controllers\GedController;
use App\Http\Controllers\ParametresController;
use App\Http\Controllers\EtudiantsController;
use App\Http\Controllers\AbonnementController;
use Illuminate\Support\Facades\Route;

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/',       [LoginController::class, 'showLogin'])->name('login');
Route::get('/login',  [LoginController::class, 'showLogin']);
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// ─── Sélection établissement ──────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/etablissement',              [EtablissementController::class, 'show'])->name('etablissement.select');
    Route::post('/etablissement/select',      [EtablissementController::class, 'select'])->name('etablissement.store');
    Route::get('/etablissement/changer',      [EtablissementController::class, 'change'])->name('etablissement.change');
    Route::post('/etablissement/{id}/logo',   [EtablissementInfoController::class, 'updateLogoById'])->name('etablissement.logo.byId');
});

// ─── Application principale (auth + établissement requis) ────────────────────
Route::middleware(['auth', 'etab'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Utilisateurs ──────────────────────────────────────
    Route::get   ('/utilisateurs',                [UtilisateursController::class, 'index']  )->name('utilisateurs.index');
    Route::post  ('/utilisateurs',                [UtilisateursController::class, 'store']  )->name('utilisateurs.store');
    Route::put   ('/utilisateurs/{id}',           [UtilisateursController::class, 'update'] )->name('utilisateurs.update');
    Route::delete('/utilisateurs/{id}',           [UtilisateursController::class, 'destroy'])->name('utilisateurs.destroy');
    Route::patch ('/utilisateurs/{id}/statut',    [UtilisateursController::class, 'toggleStatut'])->name('utilisateurs.toggleStatut');

    // ── Rôles & Permissions ───────────────────────────────
    Route::get   ('/roles',         [RolesController::class, 'index']  )->name('roles.index');
    Route::post  ('/roles',         [RolesController::class, 'store']  )->name('roles.store');
    Route::put   ('/roles/{id}',    [RolesController::class, 'update'] )->name('roles.update');
    Route::delete('/roles/{id}',    [RolesController::class, 'destroy'])->name('roles.destroy');

    // ── Direction ─────────────────────────────────────────
    Route::get   ('/direction',         [DirectionController::class, 'index']  )->name('direction.index');
    Route::post  ('/direction',         [DirectionController::class, 'store']  )->name('direction.store');
    Route::put   ('/direction/{id}',    [DirectionController::class, 'update'] )->name('direction.update');
    Route::delete('/direction/{id}',    [DirectionController::class, 'destroy'])->name('direction.destroy');

    // ── Historique ────────────────────────────────────────
    Route::get('/historique', [HistoriqueController::class, 'index'])->name('historique.index');

    // ── Établissement — Informations ──────────────────────
    Route::get ('/etablissement-info',          [EtablissementInfoController::class, 'informations'])->name('etablissement.informations');
    Route::post('/etablissement-info',          [EtablissementInfoController::class, 'updateInformations'])->name('etablissement.informations.update');
    Route::post('/etablissement-info/logo',     [EtablissementInfoController::class, 'updateLogo'])->name('etablissement.logo.update');

    // ── Établissement — Couleurs ──────────────────────────
    Route::get   ('/etablissement-couleurs',         [EtablissementInfoController::class, 'couleurs'])->name('etablissement.couleurs');
    Route::post  ('/etablissement-couleurs',         [EtablissementInfoController::class, 'saveCouleurs'])->name('etablissement.couleurs.save');
    Route::put   ('/etablissement-couleurs/{id}',    [EtablissementInfoController::class, 'updateCouleur'])->name('etablissement.couleurs.update');
    Route::delete('/etablissement-couleurs/{id}',    [EtablissementInfoController::class, 'deleteCouleur'])->name('etablissement.couleurs.delete');

    // ── Salles ────────────────────────────────────────────
    Route::get   ('/salles',              [SallesController::class, 'index'])->name('salles.index');
    Route::post  ('/salles',              [SallesController::class, 'store'])->name('salles.store');
    Route::put   ('/salles/{id}',         [SallesController::class, 'update'])->name('salles.update');
    Route::delete('/salles/{id}',         [SallesController::class, 'destroy'])->name('salles.destroy');
    Route::patch ('/salles/{id}/statut',  [SallesController::class, 'toggleStatut'])->name('salles.toggleStatut');

    // ── Académique — Filières ─────────────────────────────
    Route::get   ('/filieres',            [AcademiqueController::class, 'filieres'])->name('filieres.index');
    Route::post  ('/filieres',            [AcademiqueController::class, 'storeFiliere'])->name('filieres.store');
    Route::put   ('/filieres/{id}',       [AcademiqueController::class, 'updateFiliere'])->name('filieres.update');
    Route::delete('/filieres/{id}',       [AcademiqueController::class, 'destroyFiliere'])->name('filieres.destroy');
    Route::patch ('/filieres/{id}/statut',[AcademiqueController::class, 'toggleFiliere'])->name('filieres.toggle');

    // ── Académique — Niveaux ──────────────────────────────
    Route::get   ('/niveaux',             [AcademiqueController::class, 'niveaux'])->name('niveaux.index');
    Route::post  ('/niveaux',             [AcademiqueController::class, 'storeNiveau'])->name('niveaux.store');
    Route::put   ('/niveaux/{id}',        [AcademiqueController::class, 'updateNiveau'])->name('niveaux.update');
    Route::delete('/niveaux/{id}',        [AcademiqueController::class, 'destroyNiveau'])->name('niveaux.destroy');
    Route::patch ('/niveaux/{id}/statut', [AcademiqueController::class, 'toggleNiveau'])->name('niveaux.toggle');

    // ── Académique — Années Scolaires ────────────────────
    Route::get   ('/annees-scolaires',           [AcademiqueController::class, 'annees'])->name('annees.index');
    Route::post  ('/annees-scolaires',           [AcademiqueController::class, 'storeAnnee'])->name('annees.store');
    Route::put   ('/annees-scolaires/{id}',      [AcademiqueController::class, 'updateAnnee'])->name('annees.update');
    Route::delete('/annees-scolaires/{id}',      [AcademiqueController::class, 'destroyAnnee'])->name('annees.destroy');
    Route::patch ('/annees-scolaires/{id}/activer',[AcademiqueController::class, 'activerAnnee'])->name('annees.activer');

    // ── Académique — Découpage Année ─────────────────────
    Route::get   ('/decoupage-annee',       [AcademiqueController::class, 'decoupage'])->name('decoupage.index');
    Route::post  ('/decoupage-annee',       [AcademiqueController::class, 'storeDecoupage'])->name('decoupage.store');
    Route::put   ('/decoupage-annee/{id}',  [AcademiqueController::class, 'updateDecoupage'])->name('decoupage.update');
    Route::delete('/decoupage-annee/{id}',  [AcademiqueController::class, 'destroyDecoupage'])->name('decoupage.destroy');

    // ── Académique — Classes ──────────────────────────────
    Route::get   ('/classes',              [AcademiqueController::class, 'classes'])->name('classes.index');
    Route::post  ('/classes',              [AcademiqueController::class, 'storeClasse'])->name('classes.store');
    Route::put   ('/classes/{id}',         [AcademiqueController::class, 'updateClasse'])->name('classes.update');
    Route::delete('/classes/{id}',         [AcademiqueController::class, 'destroyClasse'])->name('classes.destroy');
    Route::patch ('/classes/{id}/statut',  [AcademiqueController::class, 'toggleClasse'])->name('classes.toggle');
    Route::get   ('/classes/{id}/etudiants',[AcademiqueController::class, 'classeEtudiants'])->name('classes.etudiants');

    // ── Scolarité — Bourses ───────────────────────────────
    Route::get   ('/bourses',              [ScolariteController::class, 'bourses'])->name('bourses.index');
    Route::post  ('/bourses',              [ScolariteController::class, 'storeBourse'])->name('bourses.store');
    Route::put   ('/bourses/{id}',         [ScolariteController::class, 'updateBourse'])->name('bourses.update');
    Route::delete('/bourses/{id}',         [ScolariteController::class, 'destroyBourse'])->name('bourses.destroy');

    // ── Scolarité — Frais de Scolarité ────────────────────
    Route::get   ('/frais-scolarite',         [ScolariteController::class, 'frais'])->name('frais.index');
    Route::post  ('/frais-scolarite',         [ScolariteController::class, 'storeFrais'])->name('frais.store');
    Route::put   ('/frais-scolarite/{id}',    [ScolariteController::class, 'updateFrais'])->name('frais.update');
    Route::delete('/frais-scolarite/{id}',    [ScolariteController::class, 'destroyFrais'])->name('frais.destroy');
    Route::post  ('/type-frais',              [ScolariteController::class, 'storeTypeFrais'])->name('typefrais.store');

    // ── Scolarité — Modalités Paiement ────────────────────
    Route::get   ('/modalites-paiement',         [ScolariteController::class, 'modalites'])->name('modalites.index');
    Route::post  ('/modalites-paiement',         [ScolariteController::class, 'storeModalite'])->name('modalites.store');
    Route::put   ('/modalites-paiement/{id}',    [ScolariteController::class, 'updateModalite'])->name('modalites.update');
    Route::delete('/modalites-paiement/{id}',    [ScolariteController::class, 'destroyModalite'])->name('modalites.destroy');

    // ── Académique — Unités d'Enseignement ───────────────
    Route::get   ('/ue',                   [AcademiqueController::class, 'ue'])->name('ue.index');
    Route::post  ('/ue',                   [AcademiqueController::class, 'storeUe'])->name('ue.store');
    Route::put   ('/ue/{id}',              [AcademiqueController::class, 'updateUe'])->name('ue.update');
    Route::delete('/ue/{id}',              [AcademiqueController::class, 'destroyUe'])->name('ue.destroy');
    Route::patch ('/ue/{id}/statut',       [AcademiqueController::class, 'toggleUe'])->name('ue.toggle');

    // ── Académique — Matières ─────────────────────────────
    Route::get   ('/matieres',             [AcademiqueController::class, 'matieres'])->name('matieres.index');
    Route::post  ('/matieres',             [AcademiqueController::class, 'storeMatiere'])->name('matieres.store');
    Route::put   ('/matieres/{id}',        [AcademiqueController::class, 'updateMatiere'])->name('matieres.update');
    Route::delete('/matieres/{id}',        [AcademiqueController::class, 'destroyMatiere'])->name('matieres.destroy');
    Route::patch ('/matieres/{id}/statut', [AcademiqueController::class, 'toggleMatiere'])->name('matieres.toggle');

    // ── Enseignants ───────────────────────────────────────
    Route::get   ('/enseignants',          [EnseignantsController::class, 'index']  )->name('enseignants.index');
    Route::post  ('/enseignants',          [EnseignantsController::class, 'store']  )->name('enseignants.store');
    Route::put   ('/enseignants/{id}',     [EnseignantsController::class, 'update'] )->name('enseignants.update');
    Route::delete('/enseignants/{id}',     [EnseignantsController::class, 'destroy'])->name('enseignants.destroy');

    // ── Affectations Enseignants ──────────────────────────
    Route::get   ('/affectations',         [EnseignantsController::class, 'affectations']     )->name('affectations.index');
    Route::post  ('/affectations',         [EnseignantsController::class, 'storeAffectation'] )->name('affectations.store');
    Route::put   ('/affectations/{id}',    [EnseignantsController::class, 'updateAffectation'])->name('affectations.update');
    Route::delete('/affectations/{id}',    [EnseignantsController::class, 'destroyAffectation'])->name('affectations.destroy');

    // ── Volume Horaire (Pointage) ─────────────────────────
    Route::get   ('/volume-horaire',             [EnseignantsController::class, 'volumeHoraire']  )->name('volume.index');
    Route::post  ('/volume-horaire',             [EnseignantsController::class, 'storePointage']  )->name('volume.store');
    Route::put   ('/volume-horaire/{id}',        [EnseignantsController::class, 'updatePointage'] )->name('volume.update');
    Route::delete('/volume-horaire/{id}',        [EnseignantsController::class, 'destroyPointage'])->name('volume.destroy');
    Route::post  ('/comptabilite-horaire',       [EnseignantsController::class, 'storeCompta']    )->name('compta.store');
    Route::put   ('/comptabilite-horaire/{id}',  [EnseignantsController::class, 'updateCompta']   )->name('compta.update');
    Route::delete('/comptabilite-horaire/{id}',  [EnseignantsController::class, 'destroyCompta']  )->name('compta.destroy');

    // ── Emploi du Temps ───────────────────────────────────
    Route::get   ('/emploi-du-temps',      [EnseignantsController::class, 'emploiDuTemps'])->name('emploi.index');
    Route::post  ('/emploi-du-temps',      [EnseignantsController::class, 'storeEmploi'] )->name('emploi.store');
    Route::put   ('/emploi-du-temps/{id}', [EnseignantsController::class, 'updateEmploi'])->name('emploi.update');
    Route::delete('/emploi-du-temps/{id}', [EnseignantsController::class, 'destroyEmploi'])->name('emploi.destroy');

    // ── Salaires Enseignants ──────────────────────────────
    Route::get   ('/salaires-enseignants',            [EnseignantsController::class, 'salaires']     )->name('salaires.index');
    Route::post  ('/salaires-enseignants',            [EnseignantsController::class, 'storeSalaire'] )->name('salaires.store');
    Route::put   ('/salaires-enseignants/{id}',       [EnseignantsController::class, 'updateSalaire'])->name('salaires.update');
    Route::delete('/salaires-enseignants/{id}',       [EnseignantsController::class, 'destroySalaire'])->name('salaires.destroy');
    Route::patch ('/salaires-enseignants/{id}/payer', [EnseignantsController::class, 'payerSalaire'] )->name('salaires.payer');

    // ── Évaluations — Notes ────────────────────────────────
    Route::get   ('/notes',       [EvaluationsController::class, 'notes']         )->name('notes.index');
    Route::post  ('/notes',       [EvaluationsController::class, 'storeNote']     )->name('notes.store');
    Route::put   ('/notes/{id}',  [EvaluationsController::class, 'updateNote']    )->name('notes.update');
    Route::delete('/notes/{id}',  [EvaluationsController::class, 'destroyNote']   )->name('notes.destroy');
    Route::post  ('/notes/bulk',  [EvaluationsController::class, 'storeNotesBulk'])->name('notes.bulk');

    // ── Évaluations — Moyennes ─────────────────────────────
    Route::get ('/moyennes',                  [EvaluationsController::class, 'moyennes'])->name('moyennes.index');
    Route::post('/moyennes/classe/{idClasse}', [EvaluationsController::class, 'calculerMoyennesClasse'])->name('moyennes.calculer');
    Route::post('/moyennes/classe/{idClasse}/credits', [EvaluationsController::class, 'genererCreditsClasse'])->name('moyennes.credits');

    // ── Délibérations ─────────────────────────────────────────────────────
    Route::get('/deliberations', [EvaluationsController::class, 'deliberationsIndex'])->name('deliberations.index');
    Route::post  ('/deliberations/decision',         [EvaluationsController::class, 'saveDecision']      )->name('deliberations.decision');
    Route::post  ('/deliberations/promouvoir',        [EvaluationsController::class, 'promouvoirEtudiant'])->name('deliberations.promouvoir');
    Route::post  ('/deliberations/promouvoir-classe', [EvaluationsController::class, 'promouvoirClasse']  )->name('deliberations.promouvoirClasse');

    // ── Évaluations — Avancé ───────────────────────────────
    Route::get   ('/evaluations-avance',        [EvaluationsController::class, 'avance']           )->name('avance.index');
    Route::post  ('/sessions-rattrapage',       [EvaluationsController::class, 'storeRattrapage']   )->name('rattrapage.store');
    Route::put   ('/sessions-rattrapage/{id}',  [EvaluationsController::class, 'updateRattrapage']  )->name('rattrapage.update');
    Route::delete('/sessions-rattrapage/{id}',  [EvaluationsController::class, 'destroyRattrapage']  )->name('rattrapage.destroy');

    // ── Finance ───────────────────────────────────────────────────────────
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/echeanciers',     [FinanceController::class, 'echeanciersIndex'])->name('echeanciers');
        Route::get('/tranches-prevues',[FinanceController::class, 'tranchesPrevuesIndex'])->name('tranches_prevues');
        Route::get('/paiements',       [FinanceController::class, 'paiementsIndex'])->name('paiements');
        Route::post('/paiements',      [FinanceController::class, 'storePaiement'])->name('paiements.store');
        Route::get('/factures',        [FinanceController::class, 'facturesIndex'])->name('factures');
        Route::post('/factures',       [FinanceController::class, 'storeFacture'])->name('factures.store');
    });

    // ── Achats ────────────────────────────────────────────────────────────
    Route::prefix('achats')->name('achats.')->group(function () {
        Route::get('/', [AchatsController::class, 'index'])->name('index');
        
        // Fournisseurs
        Route::post('/fournisseurs', [AchatsController::class, 'storeFournisseur'])->name('fournisseurs.store');
        Route::put('/fournisseurs/{id}', [AchatsController::class, 'updateFournisseur'])->name('fournisseurs.update');
        Route::delete('/fournisseurs/{id}', [AchatsController::class, 'destroyFournisseur'])->name('fournisseurs.destroy');

        // Commandes
        Route::post('/commandes', [AchatsController::class, 'storeCommande'])->name('commandes.store');
        Route::put('/commandes/{id}', [AchatsController::class, 'updateCommande'])->name('commandes.update');
        Route::delete('/commandes/{id}', [AchatsController::class, 'destroyCommande'])->name('commandes.destroy');

        // Lignes
        Route::post('/commandes/{idCommande}/lignes', [AchatsController::class, 'addLigne'])->name('commandes.lignes.store');
        Route::delete('/commandes/{idCommande}/lignes/{idLigne}', [AchatsController::class, 'removeLigne'])->name('commandes.lignes.destroy');

        // Réceptions
        Route::post('/receptions', [AchatsController::class, 'storeReception'])->name('receptions.store');
        Route::put('/receptions/{id}', [AchatsController::class, 'updateReception'])->name('receptions.update');
        Route::delete('/receptions/{id}', [AchatsController::class, 'destroyReception'])->name('receptions.destroy');

        // Factures
        Route::post('/factures', [AchatsController::class, 'storeFacture'])->name('factures.store');
        Route::put('/factures/{id}', [AchatsController::class, 'updateFacture'])->name('factures.update');
        Route::delete('/factures/{id}', [AchatsController::class, 'destroyFacture'])->name('factures.destroy');

        // Signatures
        Route::post('/signatures', [AchatsController::class, 'storeSignature'])->name('signatures.store');
        Route::put('/signatures/{id}', [AchatsController::class, 'updateSignature'])->name('signatures.update');
        Route::delete('/signatures/{id}', [AchatsController::class, 'destroySignature'])->name('signatures.destroy');
    });

    // ── Trésorerie ──────────────────────────────────────────────────────────
    Route::prefix('tresorerie')->name('tresorerie.')->group(function () {
        Route::get   ('/operations',        [TresorerieController::class, 'operations']      )->name('operations');
        Route::post  ('/operations',        [TresorerieController::class, 'storeOperation']   )->name('operations.store');
        Route::put   ('/operations/{id}',   [TresorerieController::class, 'updateOperation']  )->name('operations.update');
        Route::delete('/operations/{id}',   [TresorerieController::class, 'destroyOperation']  )->name('operations.destroy');
        Route::post  ('/init-plan-comptable', [TresorerieController::class, 'initPlanComptable'])->name('init');
        Route::post  ('/generer-ecritures', [TresorerieController::class, 'genererEcritures']  )->name('genererEcritures');

        Route::get   ('/categories-bilan',    [TresorerieController::class, 'categoriesBilan'])->name('categoriesBilan');
        Route::post  ('/categories',          [TresorerieController::class, 'storeCategorie'] )->name('categories.store');
        Route::put   ('/categories/{id}',     [TresorerieController::class, 'updateCategorie'])->name('categories.update');
        Route::delete('/categories/{id}',     [TresorerieController::class, 'destroyCategorie'])->name('categories.destroy');

        Route::get   ('/plan-comptable',     [TresorerieController::class, 'planComptable'])->name('planComptable');
        Route::post  ('/comptes',            [TresorerieController::class, 'storeCompte']  )->name('comptes.store');
        Route::put   ('/comptes/{id}',       [TresorerieController::class, 'updateCompte'] )->name('comptes.update');
        Route::delete('/comptes/{id}',       [TresorerieController::class, 'destroyCompte']  )->name('comptes.destroy');

        Route::get   ('/parametrage',           [TresorerieController::class, 'parametrage']              )->name('parametrage');
        Route::post  ('/parametrage',            [TresorerieController::class, 'storeParametrage']         )->name('parametrage.store');
        Route::post  ('/parametrage/defauts',    [TresorerieController::class, 'appliquerDefautsParametrage'])->name('parametrage.defauts');

        Route::get('/journaux',     [TresorerieController::class, 'journaux']   )->name('journaux');
        Route::get('/grand-livre',  [TresorerieController::class, 'grandLivre'] )->name('grandLivre');
        Route::get('/export-sage',  [TresorerieController::class, 'exportSage'] )->name('exportSage');
    });

    // ── Gestion de Stock ────────────────────────────────────────────────────
    Route::prefix('stocks')->name('stocks.')->group(function () {
        Route::get   ('/',                  [StocksController::class, 'index']              )->name('index');

        Route::post  ('/types',             [StocksController::class, 'storeType']          )->name('types.store');
        Route::put   ('/types/{id}',        [StocksController::class, 'updateType']         )->name('types.update');
        Route::delete('/types/{id}',        [StocksController::class, 'destroyType']         )->name('types.destroy');

        Route::post  ('/articles',          [StocksController::class, 'storeArticle']       )->name('articles.store');
        Route::put   ('/articles/{id}',     [StocksController::class, 'updateArticle']      )->name('articles.update');
        Route::delete('/articles/{id}',     [StocksController::class, 'destroyArticle']      )->name('articles.destroy');

        Route::post  ('/variants',          [StocksController::class, 'storeVariant']       )->name('variants.store');
        Route::put   ('/variants/{id}',     [StocksController::class, 'updateVariant']      )->name('variants.update');
        Route::delete('/variants/{id}',     [StocksController::class, 'destroyVariant']      )->name('variants.destroy');

        Route::post  ('/adjust',            [StocksController::class, 'adjustStock']        )->name('adjust');
        Route::post  ('/reappro',           [StocksController::class, 'reapproStock']        )->name('reappro');
        Route::delete('/stock/{id}',        [StocksController::class, 'destroyStock']         )->name('stock.destroy');

        Route::post  ('/ventes',            [StocksController::class, 'storeVente']         )->name('ventes.store');
        Route::put   ('/ventes/{id}',       [StocksController::class, 'updateVente']        )->name('ventes.update');
        Route::delete('/ventes/{id}',       [StocksController::class, 'destroyVente']         )->name('ventes.destroy');

        Route::post  ('/distribution/toggle',      [StocksController::class, 'toggleRecuperation'])->name('distribution.toggle');
        Route::post  ('/distribution/marquer-tous', [StocksController::class, 'marquerTous']       )->name('distribution.marquerTous');
    });

    // ── GED ───────────────────────────────────────────────────────────────
    Route::prefix('ged')->name('ged.')->group(function () {
        Route::get('/', [GedController::class, 'index'])->name('index');
        
        // Dossiers
        Route::post('/dossiers', [GedController::class, 'storeDossier'])->name('dossiers.store');
        Route::put('/dossiers/{id}', [GedController::class, 'updateDossier'])->name('dossiers.update');
        Route::delete('/dossiers/{id}', [GedController::class, 'destroyDossier'])->name('dossiers.destroy');

        // Documents
        Route::post('/documents', [GedController::class, 'storeDocument'])->name('documents.store');
        Route::post('/documents/{id}', [GedController::class, 'updateDocument'])->name('documents.update'); // POST to support file uploads in multipart
        Route::delete('/documents/{id}', [GedController::class, 'destroyDocument'])->name('documents.destroy');
    });

    // ── Paramètres ────────────────────────────────────────────────────────
    Route::prefix('parametres')->name('parametres.')->group(function () {
        Route::get('/', [ParametresController::class, 'index'])->name('index');
        Route::post('/profil', [ParametresController::class, 'updateCompte'])->name('profil.update');
        Route::post('/motdepasse', [ParametresController::class, 'updatePassword'])->name('password.update');

        // Statuts
        Route::post('/statuts', [ParametresController::class, 'storeStatut'])->name('statuts.store');
        Route::put('/statuts/{id}', [ParametresController::class, 'updateStatut'])->name('statuts.update');
        Route::delete('/statuts/{id}', [ParametresController::class, 'destroyStatut'])->name('statuts.destroy');

        // Types Document
        Route::post('/types-document', [ParametresController::class, 'storeTypeDocument'])->name('types-document.store');
        Route::put('/types-document/{id}', [ParametresController::class, 'updateTypeDocument'])->name('types-document.update');
        Route::delete('/types-document/{id}', [ParametresController::class, 'destroyTypeDocument'])->name('types-document.destroy');

        // Types Note
        Route::post('/types-note', [ParametresController::class, 'storeTypeNote'])->name('types-note.store');
        Route::put('/types-note/{id}', [ParametresController::class, 'updateTypeNote'])->name('types-note.update');
        Route::delete('/types-note/{id}', [ParametresController::class, 'destroyTypeNote'])->name('types-note.destroy');

        // Types Frais
        Route::post('/types-frais', [ParametresController::class, 'storeTypeFrais'])->name('types-frais.store');
        Route::put('/types-frais/{id}', [ParametresController::class, 'updateTypeFrais'])->name('types-frais.update');
        Route::delete('/types-frais/{id}', [ParametresController::class, 'destroyTypeFrais'])->name('types-frais.destroy');

        // Modes Paiement
        Route::post('/modes-paiement', [ParametresController::class, 'storeModePaiement'])->name('modes-paiement.store');
        Route::put('/modes-paiement/{id}', [ParametresController::class, 'updateModePaiement'])->name('modes-paiement.update');
        Route::delete('/modes-paiement/{id}', [ParametresController::class, 'destroyModePaiement'])->name('modes-paiement.destroy');

        // Types Abonnement
        Route::post('/types-abonnement', [ParametresController::class, 'storeTypeAbonnement'])->name('types-abonnement.store');
        Route::put('/types-abonnement/{id}', [ParametresController::class, 'updateTypeAbonnement'])->name('types-abonnement.update');
        Route::delete('/types-abonnement/{id}', [ParametresController::class, 'destroyTypeAbonnement'])->name('types-abonnement.destroy');

        // Types Article
        Route::post('/types-article', [ParametresController::class, 'storeTypeArticle'])->name('types-article.store');
        Route::put('/types-article/{id}', [ParametresController::class, 'updateTypeArticle'])->name('types-article.update');
        Route::delete('/types-article/{id}', [ParametresController::class, 'destroyTypeArticle'])->name('types-article.destroy');
    });

    // ── Étudiants ─────────────────────────────────────────────────────────
    Route::prefix('etudiants')->name('etudiants.')->group(function () {
        Route::get('/', [EtudiantsController::class, 'index'])->name('index');
        Route::post('/', [EtudiantsController::class, 'store'])->name('store');
        Route::put('/{id}', [EtudiantsController::class, 'update'])->name('update');
        Route::delete('/{id}', [EtudiantsController::class, 'destroy'])->name('destroy');

        // Parents
        Route::get('/parents', [EtudiantsController::class, 'parents'])->name('parents.index');
        Route::post('/parents', [EtudiantsController::class, 'storeParent'])->name('parents.store');
        Route::put('/parents/{id}', [EtudiantsController::class, 'updateParent'])->name('parents.update');
        Route::delete('/parents/{id}', [EtudiantsController::class, 'destroyParent'])->name('parents.destroy');
        
        // Inscriptions
        Route::get('/inscriptions', [EtudiantsController::class, 'inscriptions'])->name('inscriptions.index');
        Route::post('/inscriptions', [EtudiantsController::class, 'storeInscription'])->name('inscriptions.store');
        Route::put('/inscriptions/{id}', [EtudiantsController::class, 'updateInscription'])->name('inscriptions.update');
        Route::delete('/inscriptions/{id}', [EtudiantsController::class, 'destroyInscription'])->name('inscriptions.destroy');

        // Dossiers documents
        Route::get('/dossiers', [EtudiantsController::class, 'dossiersIndex'])->name('dossiers.index');
        Route::post('/dossier', [EtudiantsController::class, 'storeDossier'])->name('dossier.store');
        Route::delete('/dossier/{id}', [EtudiantsController::class, 'destroyDossier'])->name('dossier.destroy');
        Route::patch('/dossiers/{id}/statut', [EtudiantsController::class, 'updateDossierStatut'])->name('dossiers.status.update');

        // Boursiers
        Route::get('/boursiers', [EtudiantsController::class, 'boursiers'])->name('boursiers.index');
        Route::post('/boursiers', [EtudiantsController::class, 'storeBoursier'])->name('boursiers.store');
        Route::delete('/boursiers/{id}', [EtudiantsController::class, 'destroyBoursier'])->name('boursiers.destroy');

        // Credits
        Route::get('/credits', [EtudiantsController::class, 'credits'])->name('credits.index');
        Route::post('/credits', [EtudiantsController::class, 'storeCredit'])->name('credits.store');
        Route::put('/credits/{id}', [EtudiantsController::class, 'updateCredit'])->name('credits.update');
        Route::delete('/credits/{id}', [EtudiantsController::class, 'destroyCredit'])->name('credits.destroy');

        // Parcours
        Route::get('/parcours', [EtudiantsController::class, 'parcours'])->name('parcours.index');
        Route::post('/parcours', [EtudiantsController::class, 'storeParcours'])->name('parcours.store');
        Route::put('/parcours/{id}', [EtudiantsController::class, 'updateParcours'])->name('parcours.update');
        Route::delete('/parcours/{id}', [EtudiantsController::class, 'destroyParcours'])->name('parcours.destroy');

        // CSV Import
        Route::post('/import-csv', [EtudiantsController::class, 'importCsv'])->name('import-csv');
    });

    // ── Abonnements ──
    Route::get('/abonnements', [AbonnementController::class, 'index'])->name('abonnements.index');
    Route::post('/abonnements', [AbonnementController::class, 'store'])->name('abonnements.store');
    Route::put('/abonnements/{id}', [AbonnementController::class, 'update'])->name('abonnements.update');
    Route::delete('/abonnements/{id}', [AbonnementController::class, 'destroy'])->name('abonnements.destroy');

    // ── Documents ──
    Route::get('/documents', [AbonnementController::class, 'documentsIndex'])->name('documents.index');
    Route::get('/documents/generate', [AbonnementController::class, 'generate'])->name('documents.generate');
});

// ─── Debug statut (local uniquement) ─────────────────────────────────────────
Route::get('/debug/statuts', function () {
    if (!app()->isLocal()) abort(403);
    return response()->json(
        \Illuminate\Support\Facades\DB::table('statut')->get(),
        200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
})->middleware('auth');

// ─── Debug schema (local uniquement) ─────────────────────────────────────────
Route::get('/debug/schema', function () {
    if (!app()->isLocal()) abort(403);
    $etabId = session('etablissement_id', 1);
    $annee  = \Illuminate\Support\Facades\DB::table('annee_scolaire')
                ->where('id_etablissement', $etabId)->where('active', true)->first();

    $tables = \Illuminate\Support\Facades\DB::select(
        "SELECT table_name FROM information_schema.tables
         WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
         ORDER BY table_name"
    );

    $result = [];
    foreach ($tables as $t) {
        $cols = \Illuminate\Support\Facades\DB::select(
            "SELECT column_name, data_type
             FROM information_schema.columns
             WHERE table_schema='public' AND table_name = ?
             ORDER BY ordinal_position",
            [$t->table_name]
        );
        $result[$t->table_name] = array_column($cols, 'column_name');
    }

    // Données utiles pour comprendre les jointures
    $sample = [];
    foreach (['historique_paiement','inscription','frais_inscription','frais_scolarite','classe','niveau'] as $tbl) {
        try {
            $sample[$tbl] = \Illuminate\Support\Facades\DB::table($tbl)->limit(1)->get()->toArray();
        } catch (\Throwable $e) {
            $sample[$tbl] = ['ERROR: '.$e->getMessage()];
        }
    }

    return response()->json([
        'annee_active' => $annee,
        'tables'       => $result,
        'samples'      => $sample,
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
})->middleware('auth');
