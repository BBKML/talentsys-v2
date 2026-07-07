<?php

namespace App\Http\Controllers;

use App\Models\AffectationEnseignant;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\ComptabiliteHoraire;
use App\Models\EmploiTemps;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\Niveau;
use App\Models\Salle;
use App\Models\SalaireEnseignant;
use App\Models\VolumeHoraire;
use Illuminate\Http\Request;

class EnseignantsController extends Controller
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

    // ══ ENSEIGNANTS ══════════════════════════════════════════════════════════════

    public function index()
    {
            $enseignants = Enseignant::where('id_etablissement', $this->etabId())
                ->orderBy('nom')->orderBy('prenom')->get();
            $anneeActive = $this->anneeActive();
            return view('enseignants.enseignants', compact('enseignants', 'anneeActive'));
    }
    public function store(Request $r)
    {
        $r->validate([
            'nom'      => 'required|string|max:100',
            'prenom'   => 'required|string|max:100',
            'sexe'     => 'required|in:M,F',
            'grade'    => 'required|string|max:100',
            'email'    => 'nullable|email|max:150',
            'contact_1'=> 'nullable|string|max:50',
        ]);

        $etabId = $this->etabId();
        // Générer matricule ENS-YYYY-XXX
        $year  = date('Y');
        $count = Enseignant::where('id_etablissement', $etabId)
            ->whereRaw("matricule LIKE 'ENS-{$year}-%'")->count() + 1;
        $matricule = 'ENS-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $enseignant = Enseignant::create([
            'matricule'        => $matricule,
            'nom'              => $r->nom,
            'prenom'           => $r->prenom,
            'sexe'             => $r->sexe,
            'date_naissance'   => $r->date_naissance ?: null,
            'lieu_naissance'   => $r->lieu_naissance ?: null,
            'nationalite'      => $r->nationalite ?: null,
            'email'            => $r->email ?: null,
            'contact_1'        => $r->contact_1 ?: null,
            'contact_2'        => $r->contact_2 ?: null,
            'grade'            => $r->grade,
            'specialite'       => $r->specialite ?: null,
            'url_photo'        => $r->url_photo ?: null,
            'id_statut'        => 1,
            'id_etablissement' => $etabId,
        ]);

        return response()->json([
            'message' => 'Enseignant créé avec succès.',
            'data'    => $enseignant,
        ]);
    }

    public function update(Request $r, $id)
    {
        $r->validate([
            'nom'      => 'required|string|max:100',
            'prenom'   => 'required|string|max:100',
            'sexe'     => 'required|in:M,F',
            'grade'    => 'required|string|max:100',
            'email'    => 'nullable|email|max:150',
        ]);

        $enseignant = Enseignant::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $enseignant->update([
            'nom'            => $r->nom,
            'prenom'         => $r->prenom,
            'sexe'           => $r->sexe,
            'date_naissance' => $r->date_naissance ?: null,
            'lieu_naissance' => $r->lieu_naissance ?: null,
            'nationalite'    => $r->nationalite ?: null,
            'email'          => $r->email ?: null,
            'contact_1'      => $r->contact_1 ?: null,
            'contact_2'      => $r->contact_2 ?: null,
            'grade'          => $r->grade,
            'specialite'     => $r->specialite ?: null,
            'url_photo'      => $r->url_photo ?: $enseignant->url_photo,
        ]);

        return response()->json([
            'message' => 'Enseignant modifié.',
            'data'    => $enseignant,
        ]);
    }

    public function destroy($id)
    {
        $etabId = $this->etabId();

        // Cascade : suppression des données liées aux affectations
        $affIds = AffectationEnseignant::where('id_enseignant', $id)
            ->where('id_etablissement', $etabId)->pluck('id');

        EmploiTemps::whereIn('id_affectation_enseignant', $affIds)->delete();
        VolumeHoraire::whereIn('id_affectation_enseignant', $affIds)->delete();
        ComptabiliteHoraire::whereIn('id_affectation_enseignant', $affIds)->delete();
        AffectationEnseignant::where('id_enseignant', $id)->where('id_etablissement', $etabId)->delete();

        Enseignant::where('id', $id)->where('id_etablissement', $etabId)->delete();

        return response()->json([
            'message' => 'Enseignant supprimé.',
        ]);
    }

    // ══ AFFECTATIONS ═════════════════════════════════════════════════════════════

    public function affectations()
    {
        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();

        $affectations = AffectationEnseignant::with(['enseignant', 'matiere', 'classe', 'annee'])
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('id_enseignant')
            ->get();

        // Charger la comptabilité horaire pour calculer progression
        $comptaByAffectation = ComptabiliteHoraire::where('id_etablissement', $etabId)
            ->get()
            ->groupBy('id_affectation_enseignant');

        // Grouper par enseignant
        $grouped = $affectations->groupBy('id_enseignant')->map(function ($aff) use ($comptaByAffectation) {
            $totalQuota = $aff->sum('nombre_heure');
            $totalDone  = 0;
            foreach ($aff as $a) {
                $compta    = $comptaByAffectation->get($a->id, collect());
                $totalDone += $compta->sum('heures_realisees');
            }
            return [
                'enseignant'  => $aff->first()->enseignant,
                'affectations'=> $aff->map(function ($a) use ($comptaByAffectation) {
                    $compta = $comptaByAffectation->get($a->id, collect());
                    return [
                        'id'             => $a->id,
                        'id_matiere'     => $a->id_matiere,   // ← ajouté
                        'id_classe'      => $a->id_classe,    // ← ajouté
                        'matiere'        => $a->matiere?->libelle ?? '—',
                        'classe'         => $a->classe?->libelle ?? '—',
                        'nombre_heure'   => $a->nombre_heure,
                        'montant_horaire'=> $a->montant_horaire,
                        'heures_done'    => $compta->sum('heures_realisees'),
                    ];
                })->values(),
                'total_quota' => $totalQuota,
                'total_done'  => $totalDone,
            ];
        })->values();

        $enseignants = Enseignant::where('id_etablissement', $etabId)->orderBy('nom')->get();
        $matieres    = Matiere::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $classes     = Classe::where('id_etablissement', $etabId)
            ->when($anneeActive, fn($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('libelle')->get();

        return view('enseignants.affectations', compact('grouped', 'enseignants', 'matieres', 'classes', 'anneeActive'));
    }

    public function storeAffectation(Request $r)
    {
        $r->validate([
            'id_enseignant'  => 'required|integer',
            'id_matiere'     => 'required|integer',
            'id_classe'      => 'required|integer',
            'nombre_heure'   => 'required|numeric|min:1',
            'montant_horaire'=> 'required|numeric|min:0',
        ]);
        $anneeActive = $this->anneeActive();
        AffectationEnseignant::create([
            'id_enseignant'    => $r->id_enseignant,
            'id_annee_scolaire'=> $anneeActive?->id,
            'id_classe'        => $r->id_classe,
            'id_matiere'       => $r->id_matiere,
            'nombre_heure'     => $r->nombre_heure,
            'montant_horaire'  => $r->montant_horaire,
            'id_statut'        => 1,
            'id_etablissement' => $this->etabId(),
        ]);
        return back()->with('success', 'Affectation créée.');
    }

    public function updateAffectation(Request $r, $id)
    {
        $r->validate([
            'id_enseignant'  => 'required|integer',
            'id_matiere'     => 'required|integer',
            'id_classe'      => 'required|integer',
            'nombre_heure'   => 'required|numeric|min:1',
            'montant_horaire'=> 'required|numeric|min:0',
        ]);
        AffectationEnseignant::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'id_enseignant'  => $r->id_enseignant,
            'id_classe'      => $r->id_classe,
            'id_matiere'     => $r->id_matiere,
            'nombre_heure'   => $r->nombre_heure,
            'montant_horaire'=> $r->montant_horaire,
        ]);
        return back()->with('success', 'Affectation modifiée.');
    }

    public function destroyAffectation($id)
    {
        AffectationEnseignant::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Affectation supprimée.');
    }

    // ══ VOLUME HORAIRE ════════════════════════════════════════════════════════════

    public function volumeHoraire()
    {
        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();

        $affectations = AffectationEnseignant::with(['enseignant', 'matiere', 'classe'])
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();

        $pointages = VolumeHoraire::with(['affectation.enseignant', 'affectation.matiere'])
            ->where('id_etablissement', $etabId)
            ->orderByDesc('date_heures_arrive')
            ->get();

        $comptaList = ComptabiliteHoraire::with(['affectation.enseignant', 'affectation.matiere'])
            ->where('id_etablissement', $etabId)
            ->orderByDesc('date')
            ->get();

        // KPI comptabilité
        $totalHeures  = $comptaList->sum('heures_realisees');
        $totalMontant = $comptaList->sum('montant_total');
        $nbEntrees    = $comptaList->count();

        // Calcul heures réalisées par affectation
        $heuresByAff = $comptaList->groupBy('id_affectation_enseignant')
            ->map(fn($c) => $c->sum('heures_realisees'));

        $enseignants = Enseignant::where('id_etablissement', $etabId)->orderBy('nom')->get();
        $matieres    = Matiere::where('id_etablissement', $etabId)->orderBy('libelle')->get();

        return view('enseignants.volume-horaire', compact(
            'affectations', 'pointages', 'comptaList',
            'totalHeures', 'totalMontant', 'nbEntrees',
            'heuresByAff', 'enseignants', 'matieres', 'anneeActive'
        ));
    }

    public function storePointage(Request $r)
    {
        $r->validate([
            'id_affectation_enseignant' => 'required|integer',
            'date_heures_arrive'        => 'required|date',
            'date_heures_depart'        => 'nullable|date|after_or_equal:date_heures_arrive',
        ]);
        VolumeHoraire::create([
            'id_affectation_enseignant' => $r->id_affectation_enseignant,
            'date_heures_arrive'        => $r->date_heures_arrive,
            'date_heures_depart'        => $r->date_heures_depart ?: null,
            'id_statut'                 => 1,
            'id_etablissement'          => $this->etabId(),
        ]);
        return back()->with('success', 'Pointage créé.');
    }

    public function updatePointage(Request $r, $id)
    {
        $r->validate([
            'id_affectation_enseignant' => 'required|integer',
            'date_heures_arrive'        => 'required|date',
            'date_heures_depart'        => 'nullable|date|after_or_equal:date_heures_arrive',
        ]);
        VolumeHoraire::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'id_affectation_enseignant' => $r->id_affectation_enseignant,
            'date_heures_arrive'        => $r->date_heures_arrive,
            'date_heures_depart'        => $r->date_heures_depart ?: null,
        ]);
        return back()->with('success', 'Pointage modifié.');
    }

    public function destroyPointage($id)
    {
        VolumeHoraire::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Pointage supprimé.');
    }

    public function storeCompta(Request $r)
    {
        $r->validate([
            'id_affectation_enseignant' => 'required|integer',
            'heures_realisees'          => 'required|numeric|min:0',
            'date'                      => 'required|date',
        ]);
        $aff          = AffectationEnseignant::findOrFail($r->id_affectation_enseignant);
        $montantTotal = $r->heures_realisees * ($aff->montant_horaire ?? 0);

        ComptabiliteHoraire::create([
            'id_affectation_enseignant' => $r->id_affectation_enseignant,
            'heures_realisees'          => $r->heures_realisees,
            'montant_total'             => $montantTotal,
            'date'                      => $r->date,
            'id_statut'                 => 1,
            'id_etablissement'          => $this->etabId(),
        ]);
        return back()->with('success', 'Entrée comptable créée.');
    }

    public function updateCompta(Request $r, $id)
    {
        $r->validate([
            'id_affectation_enseignant' => 'required|integer',
            'heures_realisees'          => 'required|numeric|min:0',
            'date'                      => 'required|date',
        ]);
        $aff          = AffectationEnseignant::findOrFail($r->id_affectation_enseignant);
        $montantTotal = $r->heures_realisees * ($aff->montant_horaire ?? 0);

        ComptabiliteHoraire::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'id_affectation_enseignant' => $r->id_affectation_enseignant,
            'heures_realisees'          => $r->heures_realisees,
            'montant_total'             => $montantTotal,
            'date'                      => $r->date,
        ]);
        return back()->with('success', 'Entrée comptable modifiée.');
    }

    public function destroyCompta($id)
    {
        ComptabiliteHoraire::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Entrée comptable supprimée.');
    }

    // ══ EMPLOI DU TEMPS ═══════════════════════════════════════════════════════════

    public function emploiDuTemps()
    {
        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();

        $seances = EmploiTemps::with(['affectation.enseignant', 'affectation.matiere', 'affectation.classe', 'salle'])
            ->where('id_etablissement', $etabId)
            ->orderBy('date_heure_debut')
            ->get();

        $affectations = AffectationEnseignant::with(['enseignant', 'matiere', 'classe'])
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();

        $salles = Salle::where('id_etablissement', $etabId)
            ->where('id_statut', 1)
            ->orderBy('libelle')->get();

        $enseignants = Enseignant::where('id_etablissement', $etabId)->orderBy('nom')->get();
        $classes     = Classe::where('id_etablissement', $etabId)
            ->when($anneeActive, fn($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('libelle')->get();
        $niveaux     = Niveau::where('id_etablissement', $etabId)
            ->orderBy('ordre')->orderBy('libelle')->get();

        return view('enseignants.emploi-du-temps', compact(
            'seances', 'affectations', 'salles', 'enseignants', 'classes', 'niveaux', 'anneeActive'
        ));
    }

    public function storeEmploi(Request $r)
    {
        $r->validate([
            'id_affectation_enseignant' => 'required|integer',
            'id_salle'                  => 'nullable|integer',
            'date_heure_debut'          => 'required|date',
            'date_heure_fin'            => 'required|date|after:date_heure_debut',
            'motif_modification'        => 'nullable|string',
        ]);
        EmploiTemps::create([
            'id_affectation_enseignant' => $r->id_affectation_enseignant,
            'id_salle'                  => $r->id_salle ?: null,
            'date_heure_debut'          => $r->date_heure_debut,
            'date_heure_fin'            => $r->date_heure_fin,
            'motif_modification'        => $r->motif_modification ?: null,
            'id_statut'                 => 1,
            'id_etablissement'          => $this->etabId(),
        ]);
        return back()->with('success', 'Séance ajoutée à l\'emploi du temps.');
    }

    public function updateEmploi(Request $r, $id)
    {
        $r->validate([
            'id_affectation_enseignant' => 'required|integer',
            'id_salle'                  => 'nullable|integer',
            'date_heure_debut'          => 'required|date',
            'date_heure_fin'            => 'required|date|after:date_heure_debut',
            'motif_modification'        => 'nullable|string',
        ]);
        EmploiTemps::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'id_affectation_enseignant' => $r->id_affectation_enseignant,
            'id_salle'                  => $r->id_salle ?: null,
            'date_heure_debut'          => $r->date_heure_debut,
            'date_heure_fin'            => $r->date_heure_fin,
            'motif_modification'        => $r->motif_modification ?: null,
        ]);
        return back()->with('success', 'Séance modifiée.');
    }

    public function destroyEmploi($id)
    {
        EmploiTemps::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Séance supprimée.');
    }

    // ══ SALAIRES ══════════════════════════════════════════════════════════════════

    public function salaires()
    {
        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();

        $salaires = SalaireEnseignant::with(['enseignant', 'annee'])
            ->where('id_etablissement', $etabId)
            ->orderByDesc('mois')->orderBy('id_enseignant')
            ->get();

        $enseignants = Enseignant::where('id_etablissement', $etabId)->orderBy('nom')->get();

        return view('enseignants.salaires', compact('salaires', 'enseignants', 'anneeActive'));
    }

    public function storeSalaire(Request $r)
    {
        $r->validate([
            'id_enseignant'  => 'required|integer',
            'mois'           => 'required|string|max:7',
            'salaire_brut'   => 'required|numeric|min:0',
            'retenue_cnps'   => 'nullable|numeric|min:0',
            'retenue_ir'     => 'nullable|numeric|min:0',
            'autres_retenues'=> 'nullable|numeric|min:0',
        ]);

        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();

        $brut    = (float) $r->salaire_brut;
        $cnps    = (float) ($r->retenue_cnps    ?? 0);
        $ir      = (float) ($r->retenue_ir      ?? 0);
        $autres  = (float) ($r->autres_retenues  ?? 0);
        $net     = $brut - $cnps - $ir - $autres;

        // Générer référence SAL-YYYY-MM-XXX
        $mois  = $r->mois;
        $count = SalaireEnseignant::where('id_etablissement', $etabId)
            ->where('mois', $mois)->count() + 1;
        $ref = 'SAL-' . str_replace('-', '-', $mois) . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        SalaireEnseignant::create([
            'id_enseignant'    => $r->id_enseignant,
            'id_annee_scolaire'=> $anneeActive?->id,
            'mois'             => $mois,
            'salaire_brut'     => $brut,
            'retenue_cnps'     => $cnps,
            'retenue_ir'       => $ir,
            'autres_retenues'  => $autres,
            'salaire_net'      => $net,
            'date_paiement'    => null,
            'id_mode_paiement' => $r->id_mode_paiement ?: null,
            'statut'           => 'en_attente',
            'reference'        => $ref,
            'id_etablissement' => $etabId,
        ]);
        return back()->with('success', 'Fiche salaire créée.');
    }

    public function updateSalaire(Request $r, $id)
    {
        $r->validate([
            'id_enseignant'  => 'required|integer',
            'mois'           => 'required|string|max:7',
            'salaire_brut'   => 'required|numeric|min:0',
            'retenue_cnps'   => 'nullable|numeric|min:0',
            'retenue_ir'     => 'nullable|numeric|min:0',
            'autres_retenues'=> 'nullable|numeric|min:0',
        ]);

        $brut   = (float) $r->salaire_brut;
        $cnps   = (float) ($r->retenue_cnps    ?? 0);
        $ir     = (float) ($r->retenue_ir      ?? 0);
        $autres = (float) ($r->autres_retenues  ?? 0);
        $net    = $brut - $cnps - $ir - $autres;

        SalaireEnseignant::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'id_enseignant'   => $r->id_enseignant,
            'mois'            => $r->mois,
            'salaire_brut'    => $brut,
            'retenue_cnps'    => $cnps,
            'retenue_ir'      => $ir,
            'autres_retenues' => $autres,
            'salaire_net'     => $net,
        ]);
        return back()->with('success', 'Fiche salaire modifiée.');
    }

    public function destroySalaire($id)
    {
        SalaireEnseignant::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Fiche salaire supprimée.');
    }

    public function payerSalaire($id)
    {
        SalaireEnseignant::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'statut'         => 'payé',
            'date_paiement'  => now()->toDateString(),
        ]);
        return back()->with('success', 'Salaire marqué comme payé.');
    }
}
