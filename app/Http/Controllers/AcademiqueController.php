<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\DecoupageAnnee;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Classe;
use App\Models\Ue;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademiqueController extends Controller
{
    private function etabId() { return session('etablissement_id'); }

    // ══ FILIÈRES ═══════════════════════════════════════════════════════════════

    public function filieres()
    {
        $filieres = Filiere::where('id_etablissement', $this->etabId())->orderBy('libelle')->get();
        return view('academique.filieres', compact('filieres'));
    }

    public function storeFiliere(Request $r)
    {
        $r->validate(['libelle' => 'required|string|max:255']);
        Filiere::create(['libelle' => $r->libelle, 'id_statut' => 1, 'id_etablissement' => $this->etabId()]);
        return back()->with('success', 'Filière créée avec succès.');
    }

    public function updateFiliere(Request $r, $id)
    {
        $r->validate(['libelle' => 'required|string|max:255']);
        Filiere::where('id', $id)->where('id_etablissement', $this->etabId())->update(['libelle' => $r->libelle]);
        return back()->with('success', 'Filière modifiée.');
    }

    public function destroyFiliere($id)
    {
        Filiere::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Filière supprimée.');
    }

    public function toggleFiliere($id)
    {
        $f = Filiere::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $f->update(['id_statut' => $f->id_statut == 1 ? 2 : 1]);
        return back();
    }

    // ══ NIVEAUX ════════════════════════════════════════════════════════════════

    public function niveaux()
    {
        $niveaux = Niveau::where('id_etablissement', $this->etabId())->orderBy('ordre')->orderBy('libelle')->get();
        return view('academique.niveaux', compact('niveaux'));
    }

    public function storeNiveau(Request $r)
    {
        $r->validate(['libelle' => 'required|string|max:255', 'code' => 'required|string|max:50']);
        Niveau::create([
            'libelle'       => $r->libelle,
            'code'          => $r->code,
            'type_niveau'   => $r->type_niveau,
            'credits_requis'=> $r->credits_requis,
            'ordre'         => $r->ordre ?? 0,
            'id_statut'     => 1,
            'id_etablissement' => $this->etabId(),
        ]);
        return back()->with('success', 'Niveau créé avec succès.');
    }

    public function updateNiveau(Request $r, $id)
    {
        $r->validate(['libelle' => 'required|string|max:255', 'code' => 'required|string|max:50']);
        Niveau::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'libelle'       => $r->libelle,
            'code'          => $r->code,
            'type_niveau'   => $r->type_niveau,
            'credits_requis'=> $r->credits_requis,
            'ordre'         => $r->ordre ?? 0,
        ]);
        return back()->with('success', 'Niveau modifié.');
    }

    public function destroyNiveau($id)
    {
        Niveau::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Niveau supprimé.');
    }

    public function toggleNiveau($id)
    {
        $n = Niveau::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $n->update(['id_statut' => $n->id_statut == 1 ? 2 : 1]);
        return back();
    }

    // ══ ANNÉES SCOLAIRES ═══════════════════════════════════════════════════════

    public function annees()
    {
        $annees = AnneeScolaire::where('id_etablissement', $this->etabId())->orderByDesc('id')->get();
        return view('academique.annees', compact('annees'));
    }

    public function storeAnnee(Request $r)
    {
        $r->validate(['libelle' => 'required|string|max:100']);
        $active = $r->boolean('active');
        if ($active) {
            DB::table('annee_scolaire')->where('id_etablissement', $this->etabId())->update(['active' => false]);
        }
        AnneeScolaire::create([
            'libelle'          => $r->libelle,
            'date_debut'       => $r->date_debut ?: null,
            'date_fin'         => $r->date_fin ?: null,
            'active'           => $active,
            'id_statut'        => 1,
            'id_etablissement' => $this->etabId(),
        ]);
        return back()->with('success', 'Année scolaire créée.');
    }

    public function updateAnnee(Request $r, $id)
    {
        $r->validate(['libelle' => 'required|string|max:100']);
        $active  = $r->boolean('active');
        $etabId  = $this->etabId();
        if ($active) {
            DB::table('annee_scolaire')->where('id_etablissement', $etabId)->update(['active' => false]);
        }
        AnneeScolaire::where('id', $id)->where('id_etablissement', $etabId)->update([
            'libelle'    => $r->libelle,
            'date_debut' => $r->date_debut ?: null,
            'date_fin'   => $r->date_fin ?: null,
            'active'     => $active,
        ]);
        return back()->with('success', 'Année modifiée.');
    }

    public function destroyAnnee($id)
    {
        AnneeScolaire::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Année supprimée.');
    }

    public function activerAnnee($id)
    {
        $etabId = $this->etabId();
        DB::table('annee_scolaire')->where('id_etablissement', $etabId)->update(['active' => false]);
        AnneeScolaire::where('id', $id)->where('id_etablissement', $etabId)->update(['active' => true]);
        return back()->with('success', 'Année scolaire activée.');
    }

    // ══ DÉCOUPAGE ANNÉE ════════════════════════════════════════════════════════

    public function decoupage()
    {
        $etabId     = $this->etabId();
        $anneeActive = AnneeScolaire::where('id_etablissement', $etabId)->where('active', true)->first();
        $decoupages  = DecoupageAnnee::with('annee')
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('ordre')
            ->get();
        $annees = AnneeScolaire::where('id_etablissement', $etabId)->orderByDesc('id')->get();
        return view('academique.decoupage', compact('decoupages', 'annees', 'anneeActive'));
    }

    public function storeDecoupage(Request $r)
    {
        $r->validate(['libelle' => 'required|string|max:100', 'id_annee_scolaire' => 'required|integer']);
        DecoupageAnnee::create([
            'libelle'           => $r->libelle,
            'type'              => $r->type,
            'ordre'             => $r->ordre ?? 1,
            'date_debut'        => $r->date_debut ?: null,
            'date_fin'          => $r->date_fin ?: null,
            'id_annee_scolaire' => $r->id_annee_scolaire,
            'id_statut'         => 1,
            'id_etablissement'  => $this->etabId(),
        ]);
        return back()->with('success', 'Découpage créé.');
    }

    public function updateDecoupage(Request $r, $id)
    {
        $r->validate(['libelle' => 'required|string|max:100', 'id_annee_scolaire' => 'required|integer']);
        DecoupageAnnee::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'libelle'           => $r->libelle,
            'type'              => $r->type,
            'ordre'             => $r->ordre ?? 1,
            'date_debut'        => $r->date_debut ?: null,
            'date_fin'          => $r->date_fin ?: null,
            'id_annee_scolaire' => $r->id_annee_scolaire,
        ]);
        return back()->with('success', 'Découpage modifié.');
    }

    public function destroyDecoupage($id)
    {
        DecoupageAnnee::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Découpage supprimé.');
    }

    // ══ CLASSES ════════════════════════════════════════════════════════════════

    public function classes()
    {
        $etabId  = $this->etabId();
        $classes  = Classe::with(['filiere', 'niveau', 'annee'])
            ->where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $filieres = Filiere::where('id_etablissement', $etabId)->where('id_statut', 1)->orderBy('libelle')->get();
        $niveaux  = Niveau::where('id_etablissement', $etabId)->where('id_statut', 1)->orderBy('ordre')->get();
        return view('academique.classes', compact('classes', 'filieres', 'niveaux'));
    }

    public function classeEtudiants($id)
    {
        $etabId = $this->etabId();
        $classe = Classe::with(['filiere', 'niveau'])
            ->where('id', $id)->where('id_etablissement', $etabId)->firstOrFail();
        $inscriptions = DB::table('inscription')
            ->join('etudiant', 'etudiant.id', '=', 'inscription.id_etudiant')
            ->leftJoin('annee_scolaire', 'annee_scolaire.id', '=', 'inscription.id_annee_scolaire')
            ->where('inscription.id_classe', $id)
            ->where('inscription.id_etablissement', $etabId)
            ->orderBy('etudiant.nom')
            ->select('etudiant.id as etudiant_id', 'etudiant.nom', 'etudiant.prenom', 'etudiant.matricule',
                     'inscription.numero_inscription', 'inscription.date_inscription',
                     'inscription.type_inscription', 'annee_scolaire.libelle as annee_libelle')
            ->get();
        return view('academique.classe-etudiants', compact('classe', 'inscriptions'));
    }

    public function storeClasse(Request $r)
    {
        $r->validate(['libelle' => 'required|string|max:255', 'id_filiere' => 'required|integer', 'id_niveau' => 'required|integer']);
        $anneeActive = AnneeScolaire::where('id_etablissement', $this->etabId())->where('active', true)->first();
        Classe::create([
            'libelle'           => $r->libelle,
            'id_annee_scolaire' => $anneeActive?->id,
            'id_filiere'        => $r->id_filiere,
            'id_niveau'         => $r->id_niveau,
            'effectif'          => 0,
            'capacite_max'      => $r->capacite_max ?: null,
            'id_statut'         => 1,
            'id_etablissement'  => $this->etabId(),
        ]);
        return back()->with('success', 'Classe créée.');
    }

    public function updateClasse(Request $r, $id)
    {
        $r->validate(['libelle' => 'required|string|max:255', 'id_filiere' => 'required|integer', 'id_niveau' => 'required|integer']);
        Classe::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'libelle'      => $r->libelle,
            'id_filiere'   => $r->id_filiere,
            'id_niveau'    => $r->id_niveau,
            'capacite_max' => $r->capacite_max ?: null,
        ]);
        return back()->with('success', 'Classe modifiée.');
    }

    public function destroyClasse($id)
    {
        Classe::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Classe supprimée.');
    }

    public function toggleClasse($id)
    {
        $c = Classe::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $c->update(['id_statut' => $c->id_statut == 1 ? 2 : 1]);
        return back();
    }

    // ══ UNITÉS D'ENSEIGNEMENT ══════════════════════════════════════════════════

    public function ue()
    {
        $etabId  = $this->etabId();
        $ues      = Ue::with(['filiere', 'niveau'])
            ->where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $filieres = Filiere::where('id_etablissement', $etabId)->where('id_statut', 1)->orderBy('libelle')->get();
        $niveaux  = Niveau::where('id_etablissement', $etabId)->where('id_statut', 1)->orderBy('ordre')->get();
        return view('academique.ue', compact('ues', 'filieres', 'niveaux'));
    }

    public function storeUe(Request $r)
    {
        $r->validate(['libelle' => 'required|string|max:255']);
        Ue::create([
            'libelle'          => $r->libelle,
            'type_ue'          => $r->type_ue,
            'credit'           => $r->credit ?: null,
            'id_filiere'       => $r->id_filiere ?: null,
            'id_niveau'        => $r->id_niveau ?: null,
            'id_statut'        => 1,
            'id_etablissement' => $this->etabId(),
        ]);
        return back()->with('success', 'Unité d\'enseignement créée.');
    }

    public function updateUe(Request $r, $id)
    {
        $r->validate(['libelle' => 'required|string|max:255']);
        Ue::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'libelle'    => $r->libelle,
            'type_ue'    => $r->type_ue,
            'credit'     => $r->credit ?: null,
            'id_filiere' => $r->id_filiere ?: null,
            'id_niveau'  => $r->id_niveau ?: null,
        ]);
        return back()->with('success', 'UE modifiée.');
    }

    public function destroyUe($id)
    {
        Ue::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'UE supprimée.');
    }

    public function toggleUe($id)
    {
        $u = Ue::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $u->update(['id_statut' => $u->id_statut == 1 ? 2 : 1]);
        return back();
    }

    // ══ MATIÈRES ═══════════════════════════════════════════════════════════════

    public function matieres()
    {
        $etabId    = $this->etabId();
        $matieres   = Matiere::with(['ue', 'filiere', 'niveau', 'decoupage'])
            ->where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $filieres   = Filiere::where('id_etablissement', $etabId)->where('id_statut', 1)->orderBy('libelle')->get();
        $niveaux    = Niveau::where('id_etablissement', $etabId)->where('id_statut', 1)->orderBy('ordre')->get();
        $ues        = Ue::where('id_etablissement', $etabId)->where('id_statut', 1)->orderBy('libelle')->get();
        $decoupages = DecoupageAnnee::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        return view('academique.matieres', compact('matieres', 'filieres', 'niveaux', 'ues', 'decoupages'));
    }

    public function storeMatiere(Request $r)
    {
        $r->validate(['libelle' => 'required|string|max:255']);
        Matiere::create([
            'libelle'           => $r->libelle,
            'coefficient'       => $r->coefficient ?? 1,
            'credit'            => $r->credit ?: null,
            'id_ue'             => $r->id_ue ?: null,
            'id_filiere'        => $r->id_filiere ?: null,
            'id_niveau'         => $r->id_niveau ?: null,
            'id_decoupage_annee'=> $r->id_decoupage_annee ?: null,
            'id_statut'         => 1,
            'id_etablissement'  => $this->etabId(),
        ]);
        return back()->with('success', 'Matière créée.');
    }

    public function updateMatiere(Request $r, $id)
    {
        $r->validate(['libelle' => 'required|string|max:255']);
        Matiere::where('id', $id)->where('id_etablissement', $this->etabId())->update([
            'libelle'           => $r->libelle,
            'coefficient'       => $r->coefficient ?? 1,
            'credit'            => $r->credit ?: null,
            'id_ue'             => $r->id_ue ?: null,
            'id_filiere'        => $r->id_filiere ?: null,
            'id_niveau'         => $r->id_niveau ?: null,
            'id_decoupage_annee'=> $r->id_decoupage_annee ?: null,
        ]);
        return back()->with('success', 'Matière modifiée.');
    }

    public function destroyMatiere($id)
    {
        Matiere::where('id', $id)->where('id_etablissement', $this->etabId())->delete();
        return back()->with('success', 'Matière supprimée.');
    }

    public function toggleMatiere($id)
    {
        $m = Matiere::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        $m->update(['id_statut' => $m->id_statut == 1 ? 2 : 1]);
        return back();
    }
}
