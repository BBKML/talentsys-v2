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
