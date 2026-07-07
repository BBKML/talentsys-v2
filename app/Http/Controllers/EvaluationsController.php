<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\CreditEtudiant;
use App\Models\DecoupageAnnee;
use App\Models\Deliberation;
use App\Models\Etablissement;
use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Inscription;
use App\Models\Matiere;
use App\Models\Moyenne;
use App\Models\Niveau;
use App\Models\Note;
use App\Models\SessionRattrapage;
use App\Models\TypeNote;
use App\Models\Ue;
use Illuminate\Http\Request;

class EvaluationsController extends Controller
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

    /**
     * Génère/actualise les crédits UE pour une classe (mirrors Flutter genererCreditsClasse).
     * Retourne le nombre de lignes credits_etudiant traitées.
     */
    private function genererCreditsClasseInterne($idClasse, $etabId)
    {
        $inscriptions = Inscription::where('id_classe', $idClasse)
            ->where('id_etablissement', $etabId)->get();

        $matieres = Matiere::where('id_etablissement', $etabId)->get();
        $ues      = Ue::where('id_etablissement', $etabId)->get()->keyBy('id');
        $moyennes = Moyenne::where('id_etablissement', $etabId)->get();

        $total = 0;
        $etab  = $this->etablissement();
        $isLMD = !$etab || strtoupper((string) $etab->systeme_academique) !== 'BTS';

        foreach ($inscriptions as $ins) {
            $matsIns = $matieres->filter(fn ($m) =>
                (string) $m->id_filiere === (string) $ins->id_filiere &&
                (string) $m->id_niveau === (string) $ins->id_niveau &&
                $m->id_ue !== null
            );
            $byUe = $matsIns->groupBy('id_ue');

            foreach ($byUe as $idUe => $matsUe) {
                $sumPoids = 0;
                $sumNote  = 0;
                foreach ($matsUe as $mat) {
                    $moy = $moyennes->first(fn ($m) =>
                        (string) $m->id_inscription === (string) $ins->id && (string) $m->id_matiere === (string) $mat->id
                    );
                    if (!$moy) {
                        continue;
                    }
                    $coef = (float) ($mat->coefficient ?? 1);
                    $sumPoids += $coef;
                    $sumNote  += ((float) $moy->moyenne) * $coef;
                }
                if ($sumPoids == 0) {
                    continue;
                }
                $avgUe  = $sumNote / $sumPoids;
                $valide = $avgUe >= 10.0;
                $credUe = (int) ($ues->get($idUe)?->credit ?? 0);

                $existing = CreditEtudiant::where('id_inscription', $ins->id)
                    ->where('id_ue', $idUe)->where('id_etablissement', $etabId)->first();

                $payload = [
                    'id_inscription'   => $ins->id,
                    'id_ue'            => $idUe,
                    'credits_obtenus'  => $valide ? $credUe : 0,
                    'valide'           => $valide,
                    'date_validation'  => $valide ? now()->toDateString() : null,
                    'id_statut'        => 1,
                    'id_etablissement' => $etabId,
                ];

                if ($existing) {
                    $existing->update($payload);
                } else {
                    CreditEtudiant::create($payload);
                }
                $total++;
            }
        }

        if ($isLMD) {
            foreach ($inscriptions as $ins) {
                $this->genererDeliberationDepuisCreditsInterne($ins, $etabId);
            }
        }

        return $total;
    }

    private function genererDeliberationDepuisCreditsInterne(Inscription $ins, $etabId)
    {
        $credits = CreditEtudiant::where('id_inscription', $ins->id)->where('id_etablissement', $etabId)->get();
        if ($credits->isEmpty()) {
            return;
        }

        $ueIds = Matiere::where('id_etablissement', $etabId)
            ->where('id_filiere', $ins->id_filiere)
            ->where('id_niveau', $ins->id_niveau)
            ->whereNotNull('id_ue')
            ->pluck('id_ue')->unique();
        if ($ueIds->isEmpty()) {
            return;
        }

        $ues = Ue::where('id_etablissement', $etabId)->whereIn('id', $ueIds)->get()->keyBy('id');

        $fondTotal = 0;
        $fondValidees = 0;
        $totalValidees = 0;
        foreach ($ueIds as $ueId) {
            $isFond = ($ues->get($ueId)?->type_ue) === 'Fondamentale';
            $rec    = $credits->first(fn ($c) => (string) $c->id_ue === (string) $ueId);
            $valide = $rec && $rec->valide;
            if ($isFond) {
                $fondTotal++;
                if ($valide) {
                    $fondValidees++;
                }
            }
            if ($valide) {
                $totalValidees++;
            }
        }

        if ($totalValidees === $ueIds->count()) {
            $decision = 'ADMIS';
        } elseif ($fondTotal > 0 && $fondValidees >= $fondTotal) {
            $decision = 'ADMIS';
        } else {
            $decision = 'AJOURNÉ';
        }

        $matieres = Matiere::where('id_etablissement', $etabId)->get();
        $moyennes = Moyenne::where('id_inscription', $ins->id)->where('id_etablissement', $etabId)->get();
        $sumPoids = 0;
        $sumNote  = 0;
        foreach ($matieres as $mat) {
            $moy = $moyennes->first(fn ($m) => (string) $m->id_matiere === (string) $mat->id);
            if (!$moy) {
                continue;
            }
            $coef = (float) ($mat->coefficient ?? 1);
            $sumPoids += $coef;
            $sumNote  += ((float) $moy->moyenne) * $coef;
        }
        $moy = $sumPoids > 0 ? $sumNote / $sumPoids : 0.0;
        $mention = $moy >= 16 ? 'Très Bien' : ($moy >= 14 ? 'Bien' : ($moy >= 12 ? 'Assez Bien' : ($moy >= 10 ? 'Passable' : 'Insuffisant')));

        $existing = Deliberation::where('id_inscription', $ins->id)->where('id_etablissement', $etabId)
            ->orderByDesc('id')->first();

        if ($existing) {
            $existing->update([
                'decision' => $decision,
                'moyenne'  => $moy > 0 ? $moy : $existing->moyenne,
                'mention'  => $moy > 0 ? $mention : $existing->mention,
            ]);
        } else {
            Deliberation::create([
                'id_inscription'   => $ins->id,
                'decision'         => $decision,
                'moyenne'          => $moy > 0 ? $moy : null,
                'mention'          => $moy > 0 ? $mention : null,
                'id_statut'        => 1,
                'id_etablissement' => $etabId,
            ]);
        }
    }

    // ══ NOTES ═══════════════════════════════════════════════════════════════════

    public function notes()
    {
        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();

        $notes = Note::with(['inscription.etudiant', 'inscription.classe', 'matiere', 'typeNote'])
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->whereHas('inscription', fn ($q2) =>
                $q2->where('id_annee_scolaire', $anneeActive->id)
            ))
            ->get();

        $inscriptions = Inscription::with('etudiant')
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();

        $classes = Classe::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('libelle')->get();

        $niveaux    = Niveau::where('id_etablissement', $etabId)->orderBy('ordre')->orderBy('libelle')->get();
        $matieres   = Matiere::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $typesNote  = TypeNote::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $decoupages = DecoupageAnnee::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('ordre')->get();

        return view('evaluations.notes', compact(
            'notes', 'inscriptions', 'classes', 'niveaux', 'matieres', 'typesNote', 'decoupages', 'anneeActive'
        ));
    }

    public function storeNote(Request $r)
    {
        $r->validate([
            'id_inscription' => 'required|integer',
            'id_matiere'     => 'required|integer',
            'id_type_note'   => 'required|integer',
            'note'           => 'required|numeric|min:0|max:20',
            'session'        => 'nullable|in:Normale,Rattrapage',
        ]);

        $note = Note::create([
            'id_inscription' => $r->id_inscription,
            'id_matiere'     => $r->id_matiere,
            'id_type_note'   => $r->id_type_note,
            'note'           => $r->note,
            'session'        => $r->session ?: 'Normale',
            'id_statut'      => 1,
            'id_etablissement' => $this->etabId(),
        ]);

        return response()->json(['message' => 'Note enregistrée.', 'data' => $note]);
    }

    public function updateNote(Request $r, $id)
    {
        $r->validate([
            'id_inscription' => 'required|integer',
            'id_matiere'     => 'required|integer',
            'id_type_note'   => 'required|integer',
            'note'           => 'required|numeric|min:0|max:20',
            'session'        => 'nullable|in:Normale,Rattrapage',
        ]);

        $note = Note::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $note->update([
            'id_inscription' => $r->id_inscription,
            'id_matiere'     => $r->id_matiere,
            'id_type_note'   => $r->id_type_note,
            'note'           => $r->note,
            'session'        => $r->session ?: 'Normale',
        ]);

        return response()->json(['message' => 'Note modifiée.', 'data' => $note]);
    }

    public function destroyNote($id)
    {
        Note::where('id', $id)->where('id_etablissement', $this->etabId())->delete();

        return response()->json(['message' => 'Note supprimée.']);
    }

    public function storeNotesBulk(Request $r)
    {
        $r->validate([
            'id_matiere'   => 'required|integer',
            'id_type_note' => 'required|integer',
            'session'      => 'nullable|in:Normale,Rattrapage',
            'notes'        => 'required|array',
            'notes.*.id_inscription' => 'required|integer',
            'notes.*.note'           => 'nullable|numeric|min:0|max:20',
        ]);

        $etabId  = $this->etabId();
        $session = $r->session ?: 'Normale';
        $count   = 0;

        foreach ($r->notes as $row) {
            if ($row['note'] === null || $row['note'] === '') {
                continue;
            }

            $existing = Note::where('id_inscription', $row['id_inscription'])
                ->where('id_matiere', $r->id_matiere)
                ->where('id_type_note', $r->id_type_note)
                ->where('session', $session)
                ->where('id_etablissement', $etabId)
                ->first();

            if ($existing) {
                $existing->update(['note' => $row['note']]);
            } else {
                Note::create([
                    'id_inscription'   => $row['id_inscription'],
                    'id_matiere'       => $r->id_matiere,
                    'id_type_note'     => $r->id_type_note,
                    'note'             => $row['note'],
                    'session'          => $session,
                    'id_statut'        => 1,
                    'id_etablissement' => $etabId,
                ]);
            }
            $count++;
        }

        return response()->json(['message' => "$count note(s) enregistrée(s).", 'count' => $count]);
    }

    // ══ MOYENNES ═══════════════════════════════════════════════════════════════

    public function moyennes()
    {
        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();

        $classes = Classe::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('libelle')->get();

        $niveaux    = Niveau::where('id_etablissement', $etabId)->orderBy('ordre')->orderBy('libelle')->get();
        $matieres   = Matiere::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $typesNote  = TypeNote::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $decoupages = DecoupageAnnee::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('ordre')->get();

        $inscriptions = Inscription::with('etudiant')
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();

        $notes = Note::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->whereHas('inscription', fn ($q2) =>
                $q2->where('id_annee_scolaire', $anneeActive->id)
            ))
            ->get();

        $moyennesSaved = Moyenne::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->whereHas('inscription', fn ($q2) =>
                $q2->where('id_annee_scolaire', $anneeActive->id)
            ))
            ->get();

        return view('evaluations.moyennes', compact(
            'classes', 'niveaux', 'matieres', 'typesNote', 'decoupages',
            'inscriptions', 'notes', 'moyennesSaved', 'anneeActive'
        ));
    }

    public function calculerMoyennesClasse(Request $r, $idClasse)
    {
        $etabId = $this->etabId();

        $inscriptionIds = Inscription::where('id_classe', $idClasse)
            ->where('id_etablissement', $etabId)
            ->pluck('id');

        if ($inscriptionIds->isEmpty()) {
            return response()->json(['count' => 0]);
        }

        $notesByInsMat = Note::whereIn('id_inscription', $inscriptionIds)
            ->where('id_etablissement', $etabId)
            ->get()
            ->groupBy(fn ($n) => $n->id_inscription.'-'.$n->id_matiere);

        $typesNote = TypeNote::where('id_etablissement', $etabId)->get()->keyBy('id');
        $matieres  = Matiere::where('id_etablissement', $etabId)->get()->keyBy('id');

        $count = 0;

        foreach ($notesByInsMat as $key => $notesGroup) {
            [$idInscription, $idMatiere] = explode('-', $key);

            $notesRattrapage = $notesGroup->filter(function ($n) {
                $s = strtolower((string) $n->session);
                return $s === 'rattrapage' || $s === '2' || str_contains($s, 'rattrap');
            });
            $notesEffectives = $notesRattrapage->isNotEmpty() ? $notesRattrapage : $notesGroup;

            $byType = $notesEffectives->groupBy(fn ($n) => $n->id_type_note ?: 0);

            $sumPoids = 0;
            $sumNote  = 0;
            foreach ($byType as $idType => $notesType) {
                $avgType = $notesType->avg('note');
                if ((int) $idType === 0) {
                    $sumPoids += 1;
                    $sumNote  += $avgType;
                } else {
                    $pct = (float) ($typesNote->get($idType)?->pourcentage ?? 1);
                    $sumPoids += $pct;
                    $sumNote  += $avgType * $pct;
                }
            }

            if ($sumPoids == 0) {
                continue;
            }

            $valeur = $sumNote / $sumPoids;

            $existing = Moyenne::where('id_inscription', $idInscription)
                ->where('id_matiere', $idMatiere)
                ->where('id_etablissement', $etabId)
                ->first();

            if ($existing) {
                $existing->update(['moyenne' => $valeur]);
            } else {
                Moyenne::create([
                    'id_inscription'     => $idInscription,
                    'id_matiere'         => $idMatiere,
                    'id_decoupage_annee' => $matieres->get($idMatiere)?->id_decoupage_annee,
                    'moyenne'            => $valeur,
                    'id_statut'          => 1,
                    'id_etablissement'   => $etabId,
                ]);
            }
            $count++;
        }

        return response()->json(['count' => $count]);
    }

    public function genererCreditsClasse($idClasse)
    {
        $etabId = $this->etabId();
        $count  = $this->genererCreditsClasseInterne($idClasse, $etabId);

        return response()->json(['count' => $count]);
    }

    // ══ AVANCÉ ═══════════════════════════════════════════════════════════════════

    public function avance()
    {
        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();
        $etab        = $this->etablissement();
        $isBTS       = $etab && strtoupper((string) $etab->systeme_academique) === 'BTS';

        $niveaux = Niveau::where('id_etablissement', $etabId)->orderBy('ordre')->orderBy('libelle')->get();
        $classes = Classe::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('libelle')->get();

        $matieres  = Matiere::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $typesNote = TypeNote::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $ues       = Ue::where('id_etablissement', $etabId)->get();
        $decoupages = DecoupageAnnee::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('ordre')->get();

        $inscriptions = Inscription::with('etudiant')
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();

        $notes = Note::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->whereHas('inscription', fn ($q2) =>
                $q2->where('id_annee_scolaire', $anneeActive->id)
            ))->get();

        $moyennesSaved = Moyenne::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->whereHas('inscription', fn ($q2) =>
                $q2->where('id_annee_scolaire', $anneeActive->id)
            ))->get();

        $creditsEtudiant = CreditEtudiant::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->whereHas('inscription', fn ($q2) =>
                $q2->where('id_annee_scolaire', $anneeActive->id)
            ))->get();

        $sessionsRattrapage = SessionRattrapage::with(['classe', 'matiere'])
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();

        return view('evaluations.avance', compact(
            'niveaux', 'classes', 'matieres', 'typesNote', 'ues', 'decoupages',
            'inscriptions', 'notes', 'moyennesSaved', 'creditsEtudiant', 'sessionsRattrapage',
            'anneeActive', 'isBTS'
        ));
    }

    // ══ DÉLIBÉRATIONS ═══════════════════════════════════════════════════════════

    public function deliberationsIndex()
    {
        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();
        $etab        = $this->etablissement();
        $isBTS       = $etab && strtoupper((string) $etab->systeme_academique) === 'BTS';

        $niveaux = Niveau::where('id_etablissement', $etabId)->orderBy('ordre')->orderBy('libelle')->get();
        $classes = Classe::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('libelle')->get();

        $matieres  = Matiere::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $ues       = Ue::where('id_etablissement', $etabId)->get();
        $filieres  = Filiere::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $decoupages = DecoupageAnnee::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('ordre')->get();

        $anneesScolaires = AnneeScolaire::where('id_etablissement', $etabId)
            ->orderBy('date_debut')->get();

        // Toutes les inscriptions (toutes années) pour détecter si déjà inscrit l'année suivante
        $toutesInscriptions = Inscription::where('id_etablissement', $etabId)->get();

        $inscriptions = Inscription::with('etudiant')
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();

        $notes = Note::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->whereHas('inscription', fn ($q2) =>
                $q2->where('id_annee_scolaire', $anneeActive->id)
            ))->get();

        $moyennesSaved = Moyenne::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->whereHas('inscription', fn ($q2) =>
                $q2->where('id_annee_scolaire', $anneeActive->id)
            ))->get();

        $creditsEtudiant = CreditEtudiant::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->whereHas('inscription', fn ($q2) =>
                $q2->where('id_annee_scolaire', $anneeActive->id)
            ))->get();

        // Données de délibération
        $deliberations = Deliberation::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->whereHas('inscription', fn ($q2) =>
                $q2->where('id_annee_scolaire', $anneeActive->id)
            ))->orderByDesc('id')->get();

        return view('deliberations.index', compact(
            'niveaux', 'classes', 'matieres', 'ues', 'filieres', 'decoupages',
            'inscriptions', 'notes', 'moyennesSaved', 'creditsEtudiant',
            'anneeActive', 'isBTS', 'anneesScolaires', 'deliberations', 'toutesInscriptions'
        ));
    }


    /**
     * Sauvegarder / mettre à jour une décision de délibération pour un étudiant.
     */
    public function saveDecision(Request $r)
    {
        $r->validate([
            'id_inscription' => 'required|integer',
            'decision'       => 'required|string|in:ADMIS,AJOURNÉ,DÉFINITIVEMENT AJOURNÉ,INCOMPLET',
            'moyenne'        => 'nullable|numeric',
            'mention'        => 'nullable|string',
        ]);

        $etabId = $this->etabId();

        $existing = Deliberation::where('id_inscription', $r->id_inscription)
            ->where('id_etablissement', $etabId)
            ->orderByDesc('id')->first();

        $payload = [
            'decision'         => $r->decision,
            'moyenne'          => $r->moyenne ?? ($existing ? $existing->moyenne : null),
            'mention'          => $r->mention  ?? ($existing ? $existing->mention  : null),
            'id_statut'        => 1,
            'id_etablissement' => $etabId,
        ];

        if ($existing) {
            $existing->update($payload);
            $delib = $existing->fresh();
        } else {
            $delib = Deliberation::create(array_merge($payload, [
                'id_inscription' => $r->id_inscription,
            ]));
        }

        return response()->json(['message' => 'Décision sauvegardée.', 'data' => $delib]);
    }

    /**
     * Promouvoir un seul étudiant vers l'année / niveau suivant.
     */
    public function promouvoirEtudiant(Request $r)
    {
        $r->validate([
            'id_inscription'   => 'required|integer',
            'id_annee_scolaire'=> 'required|integer',
            'id_filiere'       => 'required|integer',
            'id_niveau'        => 'required|integer',
            'id_classe'        => 'required|integer',
            'type_inscription' => 'nullable|string',
        ]);

        $etabId  = $this->etabId();
        $insBase = Inscription::where('id', $r->id_inscription)->where('id_etablissement', $etabId)->firstOrFail();

        // Vérifier doublon
        $deja = Inscription::where('id_etudiant', $insBase->id_etudiant)
            ->where('id_annee_scolaire', $r->id_annee_scolaire)
            ->where('id_etablissement', $etabId)->exists();

        if ($deja) {
            return response()->json(['message' => 'Cet étudiant est déjà inscrit pour cette année scolaire.'], 422);
        }

        $year   = date('Y');
        $prefix = "INS-{$year}-";
        $maxNum = Inscription::where('id_etablissement', $etabId)
            ->where('numero_inscription', 'like', "{$prefix}%")
            ->get()->reduce(function ($carry, $i) use ($prefix) {
                $v = (int) substr($i->numero_inscription, strlen($prefix));
                return $v > $carry ? $v : $carry;
            }, 0);

        $typeIns = $r->type_inscription ?: ($r->id_niveau != $insBase->id_niveau ? 'Admis' : 'En attente');

        $nouvelle = Inscription::create([
            'id_etudiant'        => $insBase->id_etudiant,
            'id_filiere'         => $r->id_filiere,
            'id_niveau'          => $r->id_niveau,
            'id_classe'          => $r->id_classe,
            'id_annee_scolaire'  => $r->id_annee_scolaire,
            'type_inscription'   => $typeIns,
            'date_inscription'   => now()->toDateString(),
            'numero_inscription' => $prefix . str_pad($maxNum + 1, 3, '0', STR_PAD_LEFT),
            'affecte'            => false,
            'id_statut'          => 1,
            'id_etablissement'   => $etabId,
        ]);

        return response()->json(['message' => 'Étudiant promu avec succès.', 'data' => $nouvelle]);
    }

    /**
     * Promouvoir toute une classe (admis → niveau suivant, ajournés → même niveau).
     */
    public function promouvoirClasse(Request $r)
    {
        $r->validate([
            'id_classe'           => 'required|integer',
            'id_annee_scolaire'   => 'required|integer',
            'id_filiere'          => 'required|integer',
            'id_niveau_admis'     => 'required|integer',
            'id_classe_admis'     => 'required|integer',
            'id_classe_redoublants' => 'nullable|integer',
        ]);

        $etabId    = $this->etabId();
        $insClasse = Inscription::where('id_classe', $r->id_classe)
            ->where('id_etablissement', $etabId)->get();

        if ($insClasse->isEmpty()) {
            return response()->json(['message' => 'Aucun étudiant dans cette classe.'], 422);
        }

        $deliberations = Deliberation::where('id_etablissement', $etabId)
            ->whereIn('id_inscription', $insClasse->pluck('id'))
            ->orderByDesc('id')->get()
            ->groupBy('id_inscription')
            ->map(fn ($d) => $d->first());

        $matieres        = Matiere::where('id_etablissement', $etabId)->get();
        $moyennesSaved   = Moyenne::where('id_etablissement', $etabId)
            ->whereIn('id_inscription', $insClasse->pluck('id'))->get();
        $creditsEtudiant = CreditEtudiant::where('id_etablissement', $etabId)
            ->whereIn('id_inscription', $insClasse->pluck('id'))->get();
        $ues             = Ue::where('id_etablissement', $etabId)->get()->keyBy('id');

        $etab  = $this->etablissement();
        $isBTS = $etab && strtoupper((string) $etab->systeme_academique) === 'BTS';

        $year   = date('Y');
        $prefix = "INS-{$year}-";
        $maxNum = Inscription::where('id_etablissement', $etabId)
            ->where('numero_inscription', 'like', "{$prefix}%")
            ->get()->reduce(fn ($carry, $i) => max($carry, (int) substr($i->numero_inscription, strlen($prefix))), 0);

        $nbAdmis = 0; $nbRedoub = 0; $nbDeja = 0; $nbErreurs = 0;

        foreach ($insClasse as $ins) {
            // Vérifier doublon
            $deja = Inscription::where('id_etudiant', $ins->id_etudiant)
                ->where('id_annee_scolaire', $r->id_annee_scolaire)
                ->where('id_etablissement', $etabId)->exists();
            if ($deja) { $nbDeja++; continue; }

            // Déterminer si admis
            $decision = $deliberations->get($ins->id)?->decision;
            $isAdmis  = false;

            if ($decision === 'ADMIS') {
                $isAdmis = true;
            } elseif ($decision === 'DÉFINITIVEMENT AJOURNÉ') {
                continue; // exclus
            } elseif ($isBTS) {
                // Calcul moyenne BTS
                $matIns = $matieres->filter(fn ($m) => (string)$m->id_filiere === (string)$ins->id_filiere && (string)$m->id_niveau === (string)$ins->id_niveau);
                $sumP = 0; $sumN = 0;
                foreach ($matIns as $mat) {
                    $moy = $moyennesSaved->first(fn ($m) => $m->id_inscription == $ins->id && $m->id_matiere == $mat->id);
                    if (!$moy) continue;
                    $coef = (float)($mat->coefficient ?? 1);
                    $sumP += $coef; $sumN += (float)$moy->moyenne * $coef;
                }
                $moyGen = $sumP > 0 ? $sumN / $sumP : 0;
                $isAdmis = $moyGen >= 10;
            } else {
                // LMD : fondamentales validées
                $ueIds = $matieres->filter(fn ($m) => (string)$m->id_filiere === (string)$ins->id_filiere && (string)$m->id_niveau === (string)$ins->id_niveau && $m->id_ue)->pluck('id_ue')->unique();
                $credits = $creditsEtudiant->where('id_inscription', $ins->id);
                $fondTotal = 0; $fondVal = 0;
                foreach ($ueIds as $ueId) {
                    $ue = $ues->get($ueId);
                    if (!$ue || $ue->type_ue !== 'Fondamentale') continue;
                    $fondTotal++;
                    if ($credits->firstWhere('id_ue', $ueId)?->valide) $fondVal++;
                }
                $isAdmis = $fondTotal > 0 && $fondVal >= $fondTotal;
            }

            try {
                $maxNum++;
                $typeIns = $isAdmis ? 'Admis' : ($decision && str_contains($decision, 'AJOURNÉ') ? 'Ajourné' : 'Redoublant');
                Inscription::create([
                    'id_etudiant'        => $ins->id_etudiant,
                    'id_filiere'         => $r->id_filiere,
                    'id_niveau'          => $isAdmis ? $r->id_niveau_admis : $ins->id_niveau,
                    'id_classe'          => $isAdmis ? $r->id_classe_admis : ($r->id_classe_redoublants ?? $r->id_classe_admis),
                    'id_annee_scolaire'  => $r->id_annee_scolaire,
                    'type_inscription'   => $typeIns,
                    'date_inscription'   => now()->toDateString(),
                    'numero_inscription' => $prefix . str_pad($maxNum, 3, '0', STR_PAD_LEFT),
                    'affecte'            => false,
                    'id_statut'          => 1,
                    'id_etablissement'   => $etabId,
                ]);
                $isAdmis ? $nbAdmis++ : $nbRedoub++;
            } catch (\Throwable $e) {
                $nbErreurs++;
            }
        }

        $total = $nbAdmis + $nbRedoub;
        $parts = [];
        if ($nbAdmis  > 0) $parts[] = "{$nbAdmis} admis";
        if ($nbRedoub > 0) $parts[] = "{$nbRedoub} redoublant(s)";
        if ($nbDeja   > 0) $parts[] = "{$nbDeja} déjà inscrits";

        $message = $nbErreurs > 0
            ? "{$nbErreurs} erreur(s). " . ($total > 0 ? "{$total} inscription(s) créée(s)." : '')
            : ($total === 0 ? 'Aucune inscription créée.' : implode(' + ', $parts) . " = {$total} inscription(s) créée(s).");

        return response()->json(['message' => $message, 'nbAdmis' => $nbAdmis, 'nbRedoub' => $nbRedoub, 'nbDeja' => $nbDeja]);
    }


    public function storeRattrapage(Request $r)
    {
        $r->validate([
            'id_classe'  => 'required|integer',
            'id_matiere' => 'required|integer',
            'date_debut' => 'nullable|date',
            'date_fin'   => 'nullable|date',
        ]);
        $anneeActive = $this->anneeActive();
        $session = SessionRattrapage::create([
            'id_annee_scolaire' => $anneeActive?->id,
            'id_classe'         => $r->id_classe,
            'id_matiere'        => $r->id_matiere,
            'date_debut'        => $r->date_debut ?: null,
            'date_fin'          => $r->date_fin ?: null,
            'id_statut'         => 1,
            'id_etablissement'  => $this->etabId(),
        ]);

        return response()->json(['message' => 'Session créée.', 'data' => $session]);
    }

    public function updateRattrapage(Request $r, $id)
    {
        $r->validate([
            'id_classe'  => 'required|integer',
            'id_matiere' => 'required|integer',
            'date_debut' => 'nullable|date',
            'date_fin'   => 'nullable|date',
        ]);
        $session = SessionRattrapage::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $session->update([
            'id_classe'  => $r->id_classe,
            'id_matiere' => $r->id_matiere,
            'date_debut' => $r->date_debut ?: null,
            'date_fin'   => $r->date_fin ?: null,
        ]);

        return response()->json(['message' => 'Session modifiée.', 'data' => $session]);
    }

    public function destroyRattrapage($id)
    {
        SessionRattrapage::where('id', $id)->where('id_etablissement', $this->etabId())->delete();

        return response()->json(['message' => 'Session supprimée.']);
    }
}
