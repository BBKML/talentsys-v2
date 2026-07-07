<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\ParentModel;
use App\Models\Niveau;
use App\Models\Classe;
use App\Models\TypeDocument;
use App\Models\Statut;
use App\Models\Dossier;
use App\Models\Inscription;
use App\Models\Bourse;
use App\Models\EtudiantBourse;
use App\Models\CreditEtudiant;
use App\Models\ParcoursScolaire;
use App\Models\Filiere;
use App\Models\AnneeScolaire;
use App\Models\Ue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EtudiantsController extends Controller
{
    private function etabId()
    {
        return session('etablissement_id', 1);
    }

    public function index()
    {
        $etabId = $this->etabId();

        $etudiants = Etudiant::where('id_etablissement', $etabId)->orderBy('nom')->get();
        $parents = ParentModel::where('id_etablissement', $etabId)->orderBy('nom')->get();
        $niveaux = Niveau::where('id_etablissement', $etabId)->orderBy('ordre')->get();
        $classes = Classe::where('id_etablissement', $etabId)->orderBy('libelle')->get();
        $typesDocument = TypeDocument::where('id_etablissement', $etabId)->get();
        $statuts = Statut::all();
        $inscriptions = Inscription::where('id_etablissement', $etabId)->get();
        $dossiers = Dossier::where('id_etablissement', $etabId)->get();

        return view('etudiants.index', compact(
            'etudiants', 'parents', 'niveaux', 'classes', 
            'typesDocument', 'statuts', 'inscriptions', 'dossiers'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'sexe' => 'required|string|in:M,F',
            'matricule' => 'required|string',
            'date_naissance' => 'required|date',
            'nationalite' => 'required|string',
            'email' => 'nullable|email',
            'contact' => 'nullable|string',
            'id_parent' => 'nullable|integer',
            'photo' => 'nullable|image|max:5120'
        ]);

        $urlPhoto = '';
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/etudiants/photos'), $filename);
            $urlPhoto = '/uploads/etudiants/photos/' . $filename;
        }

        $etudiant = Etudiant::create([
            'id_etablissement' => $this->etabId(),
            'nom' => strtoupper($request->nom),
            'prenom' => $request->prenom,
            'sexe' => $request->sexe,
            'matricule' => $request->matricule,
            'date_naissance' => $request->date_naissance,
            'nationalite' => $request->nationalite,
            'email' => $request->email ?: '',
            'contact' => $request->contact ?: '',
            'id_parent' => $request->id_parent ?: null,
            'url_photo' => $urlPhoto,
            'id_statut' => 1
        ]);

        return response()->json(['success' => true, 'message' => 'Étudiant créé.', 'data' => $etudiant]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'sexe' => 'required|string|in:M,F',
            'matricule' => 'required|string',
            'date_naissance' => 'required|date',
            'nationalite' => 'required|string',
            'email' => 'nullable|email',
            'contact' => 'nullable|string',
            'id_parent' => 'nullable|integer',
            'photo' => 'nullable|image|max:5120'
        ]);

        $etudiant = Etudiant::where('id_etablissement', $this->etabId())->findOrFail($id);
        
        $urlPhoto = $etudiant->url_photo;
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($urlPhoto && str_starts_with($urlPhoto, '/uploads/etudiants/photos/')) {
                @unlink(public_path(substr($urlPhoto, 1)));
            }
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/etudiants/photos'), $filename);
            $urlPhoto = '/uploads/etudiants/photos/' . $filename;
        }

        $etudiant->update([
            'nom' => strtoupper($request->nom),
            'prenom' => $request->prenom,
            'sexe' => $request->sexe,
            'matricule' => $request->matricule,
            'date_naissance' => $request->date_naissance,
            'nationalite' => $request->nationalite,
            'email' => $request->email ?: '',
            'contact' => $request->contact ?: '',
            'id_parent' => $request->id_parent ?: null,
            'url_photo' => $urlPhoto
        ]);

        return response()->json(['success' => true, 'message' => 'Étudiant mis à jour.', 'data' => $etudiant]);
    }

    public function destroy($id)
    {
        $etabId = $this->etabId();
        $etudiant = Etudiant::where('id_etablissement', $etabId)->findOrFail($id);

        DB::transaction(function () use ($id, $etabId, $etudiant) {
            DB::table('etudiant_bourse')->where('id_etudiant', $id)->delete();
            DB::table('parcours_scolaire')->where('id_etudiant', $id)->delete();

            $inscs = DB::table('inscription')->where('id_etudiant', $id)->where('id_etablissement', $etabId)->get();
            foreach ($inscs as $ins) {
                DB::table('dossier')->where('id_inscription', $ins->id)->delete();
                DB::table('note')->where('id_inscription', $ins->id)->delete();
                DB::table('moyenne')->where('id_inscription', $ins->id)->delete();
                DB::table('credits_etudiant')->where('id_inscription', $ins->id)->delete();
                DB::table('deliberation')->where('id_inscription', $ins->id)->delete();
                DB::table('facture')->where('id_inscription', $ins->id)->delete();

                $echs = DB::table('echeancier_scolarite')->where('id_inscription', $ins->id)->get();
                foreach ($echs as $ech) {
                    $tranches = DB::table('tranche_prevu')->where('id_echeancier_scolarite', $ech->id)->get();
                    foreach ($tranches as $t) {
                        DB::table('paiement_tranche_detail')->where('id_tranche_prevu', $t->id)->delete();
                    }
                    DB::table('tranche_prevu')->where('id_echeancier_scolarite', $ech->id)->delete();
                }
                DB::table('echeancier_scolarite')->where('id_inscription', $ins->id)->delete();

                $pays = DB::table('paiement')->where('id_inscription', $ins->id)->get();
                foreach ($pays as $p) {
                    DB::table('paiement_tranche_detail')->where('id_paiement', $p->id)->delete();
                }
                DB::table('paiement')->where('id_inscription', $ins->id)->delete();
            }

            DB::table('inscription')->where('id_etudiant', $id)->where('id_etablissement', $etabId)->delete();
            $etudiant->delete();
        });

        return response()->json(['success' => true, 'message' => 'Étudiant supprimé avec cascades de scolarité.']);
    }

    // ─── PARENTS ───
    public function parents()
    {
        $etabId = $this->etabId();
        $parents = ParentModel::where('id_etablissement', $etabId)->orderBy('nom')->get();
        return view('etudiants.parents', compact('parents'));
    }

    public function storeParent(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'sexe' => 'required|string|in:M,F',
            'contact_1' => 'required|string',
            'lien_parental' => 'required|string'
        ]);

        $parent = ParentModel::create([
            'id_etablissement' => $this->etabId(),
            'nom' => strtoupper($request->nom),
            'prenom' => $request->prenom,
            'sexe' => $request->sexe,
            'contact_1' => $request->contact_1,
            'contact_2' => $request->contact_2 ?: '',
            'email' => $request->email ?: '',
            'lien_parental' => $request->lien_parental,
            'profession' => $request->profession ?: '',
            'nationalite' => $request->nationalite ?: 'Ivoirienne',
            'id_statut' => 1
        ]);

        return response()->json(['success' => true, 'id' => $parent->id, 'nom' => $parent->nom, 'prenom' => $parent->prenom, 'message' => 'Parent créé.']);
    }

    public function updateParent(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'sexe' => 'required|string',
            'contact_1' => 'required|string',
            'lien_parental' => 'required|string'
        ]);

        $parent = ParentModel::where('id_etablissement', $this->etabId())->findOrFail($id);
        $parent->update([
            'nom' => strtoupper($request->nom),
            'prenom' => $request->prenom,
            'sexe' => $request->sexe,
            'contact_1' => $request->contact_1,
            'contact_2' => $request->contact_2 ?: '',
            'email' => $request->email ?: '',
            'lien_parental' => $request->lien_parental,
            'profession' => $request->profession ?: '',
            'nationalite' => $request->nationalite ?: 'Ivoirienne'
        ]);

        return response()->json(['success' => true, 'message' => 'Parent mis à jour.']);
    }

    public function destroyParent($id)
    {
        $parent = ParentModel::where('id_etablissement', $this->etabId())->findOrFail($id);
        Etudiant::where('id_parent', $id)->update(['id_parent' => null]);
        $parent->delete();
        return response()->json(['success' => true, 'message' => 'Parent supprimé.']);
    }

    // ─── INSCRIPTIONS ───
    public function inscriptions()
    {
        $etabId = $this->etabId();
        $inscriptions = Inscription::with(['etudiant', 'filiere', 'niveau', 'classe', 'annee'])
            ->where('id_etablissement', $etabId)->orderByDesc('id')->get();
        $etudiants = Etudiant::where('id_etablissement', $etabId)->orderBy('nom')->get();
        $parents = ParentModel::where('id_etablissement', $etabId)->orderBy('nom')->get();
        $filieres = Filiere::where('id_etablissement', $etabId)->where('id_statut', 1)->get();
        $niveaux = Niveau::where('id_etablissement', $etabId)->where('id_statut', 1)->get();
        $classes = Classe::where('id_etablissement', $etabId)->get();
        $bourses = Bourse::where('id_etablissement', $etabId)->get();
        $annees = AnneeScolaire::where('id_etablissement', $etabId)->get();

        return view('etudiants.inscriptions', compact(
            'inscriptions', 'etudiants', 'parents', 'filieres', 'niveaux', 'classes', 'bourses', 'annees'
        ));
    }

    public function storeInscription(Request $request)
    {
        $request->validate([
            'id_etudiant' => 'required|integer',
            'id_filiere' => 'required|integer',
            'id_niveau' => 'required|integer',
            'id_classe' => 'required|integer',
            'id_annee_scolaire' => 'required|integer',
            'type_inscription' => 'required|string',
            'date_inscription' => 'required|date'
        ]);

        $etabId = $this->etabId();

        $year = date('Y');
        $count = Inscription::where('id_etablissement', $etabId)->count();
        $num = 'INS-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($request, $etabId, $num) {
            $ins = Inscription::create([
                'id_etablissement' => $etabId,
                'id_etudiant' => $request->id_etudiant,
                'id_filiere' => $request->id_filiere,
                'id_niveau' => $request->id_niveau,
                'id_classe' => $request->id_classe,
                'id_annee_scolaire' => $request->id_annee_scolaire,
                'numero_inscription' => $num,
                'type_inscription' => $request->type_inscription,
                'statut_paiement' => 'En attente de paiement',
                'date_inscription' => $request->date_inscription,
                'affecte' => $request->affecte ?? true,
                'est_boursier' => $request->est_boursier ?? false,
                'id_bourse' => $request->id_bourse ?: null,
                'id_statut' => 1
            ]);

            if ($request->est_boursier && $request->id_bourse) {
                EtudiantBourse::create([
                    'id_etudiant' => $request->id_etudiant,
                    'id_bourse' => $request->id_bourse,
                    'id_annee_scolaire' => $request->id_annee_scolaire,
                    'id_etablissement' => $etabId
                ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Inscription enregistrée.']);
    }

    public function updateInscription(Request $request, $id)
    {
        $request->validate([
            'id_filiere' => 'required|integer',
            'id_niveau' => 'required|integer',
            'id_classe' => 'required|integer',
            'type_inscription' => 'required|string',
            'date_inscription' => 'required|date'
        ]);

        $ins = Inscription::where('id_etablissement', $this->etabId())->findOrFail($id);
        $ins->update([
            'id_filiere' => $request->id_filiere,
            'id_niveau' => $request->id_niveau,
            'id_classe' => $request->id_classe,
            'type_inscription' => $request->type_inscription,
            'date_inscription' => $request->date_inscription,
            'affecte' => $request->affecte ?? true,
            'est_boursier' => $request->est_boursier ?? false,
            'id_bourse' => $request->id_bourse ?: null
        ]);

        return response()->json(['success' => true, 'message' => 'Inscription mise à jour.']);
    }

    public function destroyInscription($id)
    {
        $ins = Inscription::where('id_etablissement', $this->etabId())->findOrFail($id);
        
        DB::transaction(function() use ($ins) {
            DB::table('dossier')->where('id_inscription', $ins->id)->delete();
            DB::table('note')->where('id_inscription', $ins->id)->delete();
            DB::table('moyenne')->where('id_inscription', $ins->id)->delete();
            DB::table('credits_etudiant')->where('id_inscription', $ins->id)->delete();
            DB::table('deliberation')->where('id_inscription', $ins->id)->delete();
            DB::table('facture')->where('id_inscription', $ins->id)->delete();

            $echs = DB::table('echeancier_scolarite')->where('id_inscription', $ins->id)->get();
            foreach ($echs as $ech) {
                DB::table('tranche_prevu')->where('id_echeancier_scolarite', $ech->id)->delete();
            }
            DB::table('echeancier_scolarite')->where('id_inscription', $ins->id)->delete();

            $ins->delete();
        });

        return response()->json(['success' => true, 'message' => 'Inscription supprimée.']);
    }

    // ─── DOSSIERS GLOBAL ───
    public function dossiersIndex()
    {
        $etabId = $this->etabId();
        $dossiers = Dossier::with(['etudiant', 'inscription', 'typeDocument', 'status'])
            ->where('id_etablissement', $etabId)->orderByDesc('id')->get();
        $typesDocument = TypeDocument::where('id_etablissement', $etabId)->get();
        $statuts = Statut::all();

        return view('etudiants.dossiers', compact('dossiers', 'typesDocument', 'statuts'));
    }

    public function updateDossierStatut(Request $request, $id)
    {
        $request->validate(['id_statut' => 'required|integer']);
        $dossier = Dossier::where('id_etablissement', $this->etabId())->findOrFail($id);
        $dossier->update(['id_statut' => $request->id_statut]);

        return response()->json(['success' => true, 'message' => 'Statut du dossier mis à jour.']);
    }

    // ─── BOURSIERS ───
    public function boursiers()
    {
        $etabId = $this->etabId();
        $boursiers = EtudiantBourse::with(['etudiant', 'bourse', 'anneeScolaire'])
            ->where('id_etablissement', $etabId)->get();
        $etudiants = Etudiant::where('id_etablissement', $etabId)->get();
        $bourses = Bourse::where('id_etablissement', $etabId)->get();
        $annees = AnneeScolaire::where('id_etablissement', $etabId)->get();

        return view('etudiants.boursiers', compact('boursiers', 'etudiants', 'bourses', 'annees'));
    }

    public function storeBoursier(Request $request)
    {
        $request->validate([
            'id_etudiant' => 'required|integer',
            'id_bourse' => 'required|integer',
            'id_annee_scolaire' => 'required|integer'
        ]);

        $etabId = $this->etabId();

        EtudiantBourse::create([
            'id_etudiant' => $request->id_etudiant,
            'id_bourse' => $request->id_bourse,
            'id_annee_scolaire' => $request->id_annee_scolaire,
            'id_etablissement' => $etabId
        ]);

        return response()->json(['success' => true, 'message' => 'Bourse attribuée.']);
    }

    public function destroyBoursier($id)
    {
        $b = EtudiantBourse::where('id_etablissement', $this->etabId())->findOrFail($id);
        $b->delete();
        return response()->json(['success' => true, 'message' => 'Bourse retirée.']);
    }

    // ─── CREDITS ───
    public function credits()
    {
        $etabId = $this->etabId();
        $credits = CreditEtudiant::with(['inscription.etudiant', 'ue'])
            ->where('id_etablissement', $etabId)->get();
        $inscriptions = Inscription::with('etudiant')->where('id_etablissement', $etabId)->get();
        $ues = Ue::where('id_etablissement', $etabId)->get();
        $classes = Classe::where('id_etablissement', $etabId)->get();

        return view('etudiants.credits', compact('credits', 'inscriptions', 'ues', 'classes'));
    }

    public function storeCredit(Request $request)
    {
        $request->validate([
            'id_inscription' => 'required|integer',
            'id_ue' => 'required|integer',
            'credits_obtenus' => 'required|integer',
            'valide' => 'required|boolean'
        ]);

        CreditEtudiant::create([
            'id_inscription' => $request->id_inscription,
            'id_ue' => $request->id_ue,
            'credits_obtenus' => $request->credits_obtenus,
            'valide' => $request->valide,
            'date_validation' => now()->toDateString(),
            'id_statut' => 1,
            'id_etablissement' => $this->etabId()
        ]);

        return response()->json(['success' => true, 'message' => 'Crédit enregistré.']);
    }

    public function updateCredit(Request $request, $id)
    {
        $request->validate([
            'credits_obtenus' => 'required|integer',
            'valide' => 'required|boolean'
        ]);

        $c = CreditEtudiant::where('id_etablissement', $this->etabId())->findOrFail($id);
        $c->update([
            'credits_obtenus' => $request->credits_obtenus,
            'valide' => $request->valide,
            'date_validation' => $request->valide ? now()->toDateString() : null
        ]);

        return response()->json(['success' => true, 'message' => 'Crédit mis à jour.']);
    }

    public function destroyCredit($id)
    {
        $c = CreditEtudiant::where('id_etablissement', $this->etabId())->findOrFail($id);
        $c->delete();
        return response()->json(['success' => true, 'message' => 'Crédit supprimé.']);
    }

    // ─── PARCOURS SCOLAIRE ───
    public function parcours()
    {
        $etabId = $this->etabId();
        $parcours = ParcoursScolaire::with('etudiant')->where('id_etablissement', $etabId)->get();
        $etudiants = Etudiant::where('id_etablissement', $etabId)->orderBy('nom')->get();
        $inscriptions = Inscription::with(['niveau', 'classe', 'filiere', 'annee'])
            ->where('id_etablissement', $etabId)->get();

        return view('etudiants.parcours', compact('parcours', 'etudiants', 'inscriptions'));
    }

    public function storeParcours(Request $request)
    {
        $request->validate([
            'id_etudiant' => 'required|integer',
            'etablissement' => 'required|string',
            'classe' => 'required|string',
            'annee_scolaire' => 'required|string',
            'moyenne_generale' => 'required|numeric'
        ]);

        ParcoursScolaire::create([
            'id_etudiant' => $request->id_etudiant,
            'etablissement' => $request->etablissement,
            'classe' => $request->classe,
            'annee_scolaire' => $request->annee_scolaire,
            'moyenne_generale' => $request->moyenne_generale,
            'decision' => $request->decision ?: 'Admis',
            'id_etablissement' => $this->etabId()
        ]);

        return response()->json(['success' => true, 'message' => 'Parcours enregistré.']);
    }

    public function updateParcours(Request $request, $id)
    {
        $request->validate([
            'etablissement' => 'required|string',
            'classe' => 'required|string',
            'annee_scolaire' => 'required|string',
            'moyenne_generale' => 'required|numeric'
        ]);

        $p = ParcoursScolaire::where('id_etablissement', $this->etabId())->findOrFail($id);
        $p->update([
            'etablissement' => $request->etablissement,
            'classe' => $request->classe,
            'annee_scolaire' => $request->annee_scolaire,
            'moyenne_generale' => $request->moyenne_generale,
            'decision' => $request->decision ?: 'Admis'
        ]);

        return response()->json(['success' => true, 'message' => 'Parcours mis à jour.']);
    }

    public function destroyParcours($id)
    {
        $p = ParcoursScolaire::where('id_etablissement', $this->etabId())->findOrFail($id);
        $p->delete();
        return response()->json(['success' => true, 'message' => 'Parcours supprimé.']);
    }

    // ─── CSV IMPORTS ───
    public function importCsv(Request $request)
    {
        $request->validate([
            'fichier_csv' => 'required|file'
        ]);

        $file = $request->file('fichier_csv');
        $csvData = file_get_contents($file->getPathname());
        
        if (str_starts_with($csvData, "\xEF\xBB\xBF")) {
            $csvData = substr($csvData, 3);
        }

        $lines = array_filter(array_map('trim', explode("\n", $csvData)));
        if (count($lines) < 2) {
            return response()->json(['success' => false, 'message' => 'Fichier CSV vide.']);
        }

        $sep = str_contains($lines[0], ';') ? ';' : ',';
        $headers = array_map(fn($h) => strtolower(trim(str_replace('"', '', $h))), explode($sep, array_shift($lines)));
        
        $colMap = [];
        foreach ($headers as $i => $h) {
            if (str_contains($h, 'matricule')) $colMap['matricule'] = $i;
            else if (in_array($h, ['nom', 'name', 'last_name'])) $colMap['nom'] = $i;
            else if (str_contains($h, 'prenom') || $h === 'first_name') $colMap['prenom'] = $i;
            else if (in_array($h, ['sexe', 'genre', 'gender'])) $colMap['sexe'] = $i;
            else if (str_contains($h, 'naissance') || str_contains($h, 'birth')) $colMap['date_naissance'] = $i;
            else if (str_contains($h, 'nationalite') || str_contains($h, 'nationality')) $colMap['nationalite'] = $i;
            else if (in_array($h, ['contact', 'telephone', 'phone'])) $colMap['contact'] = $i;
            else if (in_array($h, ['email', 'mail'])) $colMap['email'] = $i;
        }

        $imported = 0;
        $duplicates = 0;
        $skipped = 0;
        $etabId = $this->etabId();
        $year = date('Y');

        $existing = Etudiant::where('id_etablissement', $etabId)->pluck('matricule')->map(fn($m) => strtolower($m))->toArray();

        foreach ($lines as $line) {
            $cols = array_map(fn($c) => trim(str_replace('"', '', $c)), explode($sep, $line));
            
            $nom = isset($colMap['nom']) ? ($cols[$colMap['nom']] ?? '') : '';
            $prenom = isset($colMap['prenom']) ? ($cols[$colMap['prenom']] ?? '') : '';
            if (empty($nom) && empty($prenom)) {
                $skipped++;
                continue;
            }

            $rawMat = isset($colMap['matricule']) ? ($cols[$colMap['matricule']] ?? '') : '';
            $matricule = !empty($rawMat) ? $rawMat : 'ETU-' . $year . '-' . str_pad(count($existing) + $imported + 1, 4, '0', STR_PAD_LEFT);

            if (in_array(strtolower($matricule), $existing)) {
                $duplicates++;
                continue;
            }

            $sexe = isset($colMap['sexe']) ? ($cols[$colMap['sexe']] ?? 'M') : 'M';
            $dateNaiss = isset($colMap['date_naissance']) ? ($cols[$colMap['date_naissance']] ?? '') : '';
            
            if (preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})$/', $dateNaiss, $m)) {
                $dateNaiss = $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
            }

            Etudiant::create([
                'id_etablissement' => $etabId,
                'nom' => strtoupper($nom),
                'prenom' => $prenom,
                'sexe' => strtoupper(substr(trim($sexe), 0, 1)) === 'F' ? 'F' : 'M',
                'matricule' => $matricule,
                'date_naissance' => !empty($dateNaiss) ? $dateNaiss : date('Y-m-d'),
                'nationalite' => isset($colMap['nationalite']) ? ($cols[$colMap['nationalite']] ?? 'Ivoirienne') : 'Ivoirienne',
                'email' => isset($colMap['email']) ? ($cols[$colMap['email']] ?? '') : '',
                'contact' => isset($colMap['contact']) ? ($cols[$colMap['contact']] ?? '') : '',
                'id_statut' => 1
            ]);

            $imported++;
        }

        return response()->json([
            'success' => true, 
            'message' => "Importation terminée : $imported importé(s), $duplicates doublon(s) ignoré(s), $skipped ligne(s) vide(s)."
        ]);
    }
}
