<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Etudiant;
use App\Models\Inscription;
use App\Models\Niveau;
use App\Models\ParentModel;
use App\Models\Statut;
use Barryvdh\DomPDF\Facade\Pdf; // composer require barryvdh/laravel-dompdf
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EtudiantController extends Controller
{
    private array $csvColumns = [
        'matricule', 'nom', 'prenom', 'sexe', 'date_naissance',
        'lieu_naissance', 'nationalite', 'contact', 'email',
    ];

    private function currentEtablissementId(): ?int
    {
        return session('etablissement_id') ?? auth()->user()->id_etablissement ?? null;
    }

    /**
     * Année scolaire actuellement sélectionnée dans le header (ex: "2025-2026").
     * Adapte cette clé de session à ta gestion réelle du sélecteur.
     */
    private function currentAnneeScolaireId(): ?int
    {
        return session('annee_scolaire_id');
    }

    private function defaultStatutId(): int
    {
        return Statut::where('libelle', 'Actif')->value('id') ?? 1;
    }

    /**
     * GET /etudiants
     */
    public function index(Request $request)
    {
        
        $etablissementId = $this->currentEtablissementId();
        $anneeId = $this->currentAnneeScolaireId();

        $etudiants = Etudiant::query()
            ->when($etablissementId, fn ($q, $id) => $q->where('id_etablissement', $id))
            ->with(['statut', 'parent'])
            // ->orderByDesc('matricule')
            ->limit(50)
            ->get();
            
        // On récupère en une seule requête les inscriptions (niveau + classe) de
        // l'année scolaire courante pour tous les étudiants affichés.
        $inscriptions = Inscription::with('classe')
            ->whereIn('id_etudiant', $etudiants->pluck('id'))
            ->when($anneeId, fn ($q, $id) => $q->where('id_annee_scolaire', $id))
            ->get()
            ->keyBy('id_etudiant');

        $niveaux = Niveau::orderBy('ordre')->get(['id', 'libelle']);
        $classes = Classe::query()
            ->when($etablissementId, fn ($q, $id) => $q->where('id_etablissement', $id))
            ->when($anneeId, fn ($q, $id) => $q->where('id_annee_scolaire', $id))
            ->orderBy('libelle')
            ->get(['id', 'libelle', 'id_niveau']);

        $data = $etudiants->map(function ($e) use ($inscriptions) {
            $inscription = $inscriptions->get($e->id);

            return [
                'id' => $e->id,
                'matricule' => $e->matricule,
                'nom' => $e->nom,
                'prenom' => $e->prenom,
                'sexe' => $e->sexe ?? '',
                'date_naissance' => optional($e->date_naissance)->format('Y-m-d'),
                'lieu_naissance' => $e->lieu_naissance ?? '',
                'nationalite' => $e->nationalite ?? '',
                'contact' => $e->contact ?? '',
                'email' => $e->email ?? '',
                'id_parent' => $e->id_parent,
                'parent_nom' => $e->parent ? $e->parent->nom.' '.$e->parent->prenom : '',
                'actif' => $e->id_statut == $this->defaultStatutId(),
                'id_classe' => $inscription?->id_classe,
                'classe_libelle' => $inscription?->classe?->libelle ?? '',
                'id_niveau' => $inscription?->id_niveau,
            ];
        });

        return view('etudiant.etudiant', [
            'etudiants' => $data,
            'niveaux' => $niveaux,
            'classes' => $classes,
            'parentsList' => ParentModel::orderBy('nom')->get(['id', 'nom', 'prenom']),
        ]);
    }

    /**
     * POST /etudiants
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $data['id_etablissement'] = $this->currentEtablissementId();
        $data['id_statut'] = $this->defaultStatutId();
        $data['matricule'] = $this->generateMatricule();

        Etudiant::create($data);

        return redirect()->route('etudiants.index')->with('success', 'Étudiant ajouté avec succès.');
    }

    /**
     * PUT/PATCH /etudiants/{id}
     */
    public function update(Request $request, int $id)
    {
        $etudiant = Etudiant::findOrFail($id);

        $data = $this->validateData($request);

        $etudiant->update($data);

        return redirect()->route('etudiants.index')->with('success', 'Étudiant mis à jour avec succès.');
    }

    /**
     * DELETE /etudiants/{id}
     */
    public function destroy(int $id)
    {
        Etudiant::findOrFail($id)->delete();

        return redirect()->route('etudiants.index')->with('success', 'Étudiant supprimé avec succès.');
    }

    /**
     * PATCH /etudiants/{id}/statut
     * Bascule Actif / Inactif (bouton statut dans le tableau).
     */
    public function toggleStatut(int $id)
    {
        $etudiant = Etudiant::findOrFail($id);
        $actifId = $this->defaultStatutId();
        $inactifId = Statut::where('libelle', 'Inactif')->value('id');

        $etudiant->id_statut = $etudiant->id_statut == $actifId ? $inactifId : $actifId;
        $etudiant->save();

        return back()->with('success', 'Statut mis à jour.');
    }

    /**
     * GET /etudiants/{id}/fiche
     * Fiche complète d'un étudiant (icône "document" dans les actions).
     */
    public function fiche(int $id)
    {
        $etudiant = Etudiant::with(['parent', 'statut'])->findOrFail($id);

        return view('etudiant.fiche', compact('etudiant'));
    }

    /**
     * GET /etudiants/{id}/carte
     * Génère la carte scolaire de l'étudiant en PDF (icône "partager").
     */
    public function carte(int $id)
    {
        $etudiant = Etudiant::with(['etablissement', 'statut'])->findOrFail($id);

        $inscription = Inscription::with(['niveau', 'classe', 'filiere', 'anneeScolaire'])
            ->where('id_etudiant', $etudiant->id)
            ->when($this->currentAnneeScolaireId(), fn ($q, $id) => $q->where('id_annee_scolaire', $id))
            ->latest('id')
            ->first();

        $pdf = Pdf::loadView('etudiant.carte', compact('etudiant', 'inscription'))
            ->setPaper([0, 0, 243, 153]); // format carte

        return $pdf->stream('carte_'.$etudiant->matricule.'.pdf');
    }

    /**
     * GET /etudiants/{id}/documents
     * Dossier documents de l'étudiant (icône "dossier" dans les actions).
     * Stub minimal — à compléter selon ta gestion de fichiers (uploads, etc.).
     */
    public function documents(int $id)
    {
        $etudiant = Etudiant::findOrFail($id);

        return view('etudiant.documents', compact('etudiant'));
    }

    /**
     * GET /etudiants/csv/modele
     */
    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $this->csvColumns, ';');
            fclose($handle);
        }, 'modele_etudiants.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * GET /etudiants/csv/export
     */
    public function export(): StreamedResponse
    {
        $etudiants = Etudiant::query()
            ->when($this->currentEtablissementId(), fn ($q, $id) => $q->where('id_etablissement', $id))
            ->orderBy('nom')
            ->get();

        return response()->streamDownload(function () use ($etudiants) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $this->csvColumns, ';');

            foreach ($etudiants as $e) {
                fputcsv($handle, [
                    $e->matricule,
                    $e->nom,
                    $e->prenom,
                    $e->sexe,
                    optional($e->date_naissance)->format('Y-m-d'),
                    $e->lieu_naissance,
                    $e->nationalite,
                    $e->contact,
                    $e->email,
                ], ';');
            }

            fclose($handle);
        }, 'etudiants_export_'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * GET /etudiants/pdf/export
     */
    public function exportPdf()
    {
        $etudiants = Etudiant::query()
            ->when($this->currentEtablissementId(), fn ($q, $id) => $q->where('id_etablissement', $id))
            ->orderBy('nom')
            ->get();

        $pdf = Pdf::loadView('etudiants.export-pdf', compact('etudiants'))->setPaper('a4', 'landscape');

        return $pdf->download('liste_etudiants_'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * POST /etudiants/csv/import
     */
    public function import(Request $request)
    {
        $request->validate(['fichier' => ['required', 'file', 'mimes:csv,txt']]);

        $path = $request->file('fichier')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->with('error', 'Impossible de lire le fichier CSV.');
        }

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $header = array_map(fn ($h) => strtolower(trim($h)), fgetcsv($handle, 0, $delimiter));

        $created = 0;
        $skipped = 0;
        $etablissementId = $this->currentEtablissementId();
        $statutId = $this->defaultStatutId();

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (count(array_filter($row)) === 0) {
                    continue;
                }

                $line = array_combine(array_slice($header, 0, count($row)), $row);

                $validator = Validator::make($line, [
                    'matricule' => ['nullable', 'string', 'max:50', 'unique:etudiant,matricule'],
                    'nom' => ['required', 'string', 'max:100'],
                    'prenom' => ['required', 'string', 'max:100'],
                    'sexe' => ['nullable', 'in:M,F'],
                    'date_naissance' => ['nullable', 'date'],
                    'lieu_naissance' => ['nullable', 'string', 'max:100'],
                    'nationalite' => ['nullable', 'string', 'max:50'],
                    'contact' => ['nullable', 'string', 'max:20'],
                    'email' => ['nullable', 'email', 'max:100'],
                ]);

                if ($validator->fails()) {
                    $skipped++;
                    continue;
                }

                $clean = $validator->validated();
                $clean['matricule'] = $clean['matricule'] ?: $this->generateMatricule();
                $clean['id_etablissement'] = $etablissementId;
                $clean['id_statut'] = $statutId;

                Etudiant::create($clean);
                $created++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);

            return back()->with('error', "Échec de l'import : ".$e->getMessage());
        }

        fclose($handle);

        return back()->with(
            'success',
            "$created étudiant(s) importé(s) avec succès".($skipped ? ", $skipped ligne(s) ignorée(s)." : '.')
        );
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'sexe' => ['nullable', 'in:M,F'],
            'date_naissance' => ['nullable', 'date'],
            'lieu_naissance' => ['nullable', 'string', 'max:100'],
            'nationalite' => ['nullable', 'string', 'max:50'],
            'contact' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'id_parent' => ['nullable', 'integer', 'exists:parent,id'],
        ]);
    }

    /**
     * Génère un matricule du type MAT + année + numéro séquentiel,
     * cohérent avec le format vu sur la capture (MAT20260766).
     */
    private function generateMatricule(): string
    {
        $year = now()->format('Y');
        $last = Etudiant::where('matricule', 'like', "MAT{$year}%")
            ->orderByDesc('matricule')
            ->value('matricule');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('MAT%s%04d', $year, $seq);
    }
}
