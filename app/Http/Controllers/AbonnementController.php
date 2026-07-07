<?php

namespace App\Http\Controllers;

use App\Models\TypeAbonnement;
use App\Models\Abonnement;
use App\Models\Etablissement;
use App\Models\Etudiant;
use App\Models\Inscription;
use App\Models\Niveau;
use App\Models\Filiere;
use App\Models\Classe;
use App\Models\Statut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbonnementController extends Controller
{
    private function etabId()
    {
        return session('etablissement_id', 1);
    }

    public function index()
    {
        $etabId = $this->etabId();
        
        $types = TypeAbonnement::where('id_etablissement', $etabId)->orderBy('prix_mensuel')->get();
        $abonnements = Abonnement::with(['type', 'etablissement', 'status'])
            ->where('id_etablissement', $etabId)->get();
            
        $etablissements = Etablissement::all();
        $statuts = Statut::all();

        return view('abonnements.index', compact('types', 'abonnements', 'etablissements', 'statuts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_type_abonnement' => 'required|integer',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date'
        ]);

        $abon = Abonnement::create([
            'id_type_abonnement' => $request->id_type_abonnement,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin ?: null,
            'id_statut' => 1,
            'id_etablissement' => $this->etabId()
        ]);

        return response()->json(['success' => true, 'message' => 'Abonnement enregistré.', 'data' => $abon]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date',
            'id_statut' => 'required|integer'
        ]);

        $abon = Abonnement::where('id_etablissement', $this->etabId())->findOrFail($id);
        $abon->update([
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin ?: null,
            'id_statut' => $request->id_statut
        ]);

        return response()->json(['success' => true, 'message' => 'Abonnement mis à jour.']);
    }

    public function destroy($id)
    {
        $abon = Abonnement::where('id_etablissement', $this->etabId())->findOrFail($id);
        $abon->delete();

        return response()->json(['success' => true, 'message' => 'Abonnement supprimé.']);
    }

    // ─── DOCUMENTS ───
    public function documentsIndex()
    {
        $etabId = $this->etabId();
        
        $etudiants = Etudiant::where('id_etablissement', $etabId)->orderBy('nom')->get();
        $inscriptions = Inscription::with('etudiant')->where('id_etablissement', $etabId)->get();
        $niveaux = Niveau::where('id_etablissement', $etabId)->orderBy('ordre')->get();
        $filieres = Filiere::where('id_etablissement', $etabId)->get();

        return view('documents.index', compact('etudiants', 'inscriptions', 'niveaux', 'filieres'));
    }

    public function generate(Request $request)
    {
        $type = $request->query('type', 'bulletin');
        $studentId = $request->query('id_etudiant');
        $niveauId = $request->query('id_niveau');
        $filiereId = $request->query('id_filiere');

        $etabId = $this->etabId();
        $etablissement = Etablissement::find($etabId);
        
        $students = collect();

        if ($studentId) {
            $student = Etudiant::where('id_etablissement', $etabId)->findOrFail($studentId);
            $inscription = Inscription::with(['niveau', 'classe', 'filiere', 'annee'])
                ->where('id_etudiant', $studentId)
                ->where('id_etablissement', $etabId)
                ->first();
            $students->push([
                'etudiant' => $student,
                'inscription' => $inscription
            ]);
        } elseif ($niveauId && $filiereId) {
            $inscriptions = Inscription::with(['etudiant', 'niveau', 'classe', 'filiere', 'annee'])
                ->where('id_niveau', $niveauId)
                ->where('id_filiere', $filiereId)
                ->where('id_etablissement', $etabId)
                ->get();
            foreach ($inscriptions as $insc) {
                if ($insc->etudiant) {
                    $students->push([
                        'etudiant' => $insc->etudiant,
                        'inscription' => $insc
                    ]);
                }
            }
        }

        return view('documents.templates', compact('type', 'students', 'etablissement'));
    }
}
