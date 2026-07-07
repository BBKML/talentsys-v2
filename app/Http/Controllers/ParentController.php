<?php

namespace App\Http\Controllers;

use App\Models\ParentModel;
use App\Models\Statut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParentController extends Controller
{
    /**
     * Colonnes attendues dans le fichier CSV (modèle, import, export),
     * dans cet ordre précis.
     */
    private array $csvColumns = [
        'nom', 'prenom', 'sexe', 'contact_1', 'contact_2',
        'email', 'lien_parental', 'profession', 'nationalite',
    ];

    /**
     * Récupère l'id de l'établissement courant (sélecteur en haut de page).
     * Adapte cette méthode à ta gestion réelle de session / multi-établissement.
     */
    private function currentEtablissementId(): ?int
    {
        return session('etablissement_id') ?? auth()->user()->id_etablissement ?? null;
    }

    /**
     * Id du statut "Actif" par défaut à l'insertion d'un nouveau parent.
     */
    private function defaultStatutId(): int
    {
        return Statut::where('libelle', 'Actif')->value('id') ?? 1;
    }

    /**
     * GET /parents
     */
    public function index(Request $request)
    {
        $parents = ParentModel::query()
            ->when($this->currentEtablissementId(), fn ($q, $id) => $q->where('id_etablissement', $id))
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        return view('etudiant.parent', compact('parents'));
    }

    /**
     * POST /parents
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $data['id_etablissement'] = $this->currentEtablissementId();
        $data['id_statut'] = $this->defaultStatutId();

        ParentModel::create($data);

        return redirect()
            ->route('parents.index')
            ->with('success', 'Parent ajouté avec succès.');
    }

    /**
     * PUT/PATCH /parents/{id}
     */
    public function update(Request $request, int $id)
    {
        $parent = ParentModel::findOrFail($id);

        $data = $this->validateData($request);

        $parent->update($data);

        return redirect()
            ->route('parents.index')
            ->with('success', 'Parent mis à jour avec succès.');
    }

    /**
     * DELETE /parents/{id}
     */
    public function destroy(int $id)
    {
        $parent = ParentModel::findOrFail($id);
        $parent->delete();

        return redirect()
            ->route('parents.index')
            ->with('success', 'Parent supprimé avec succès.');
    }

    /**
     * GET /parents/csv/modele
     * Télécharge un fichier CSV vide avec juste les en-têtes attendues.
     */
    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM pour Excel
            fputcsv($handle, $this->csvColumns, ';');
            fclose($handle);
        }, 'modele_parents.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * GET /parents/csv/export
     */
    public function export(): StreamedResponse
    {
        $parents = ParentModel::query()
            ->when($this->currentEtablissementId(), fn ($q, $id) => $q->where('id_etablissement', $id))
            ->orderBy('nom')
            ->get();

        return response()->streamDownload(function () use ($parents) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $this->csvColumns, ';');

            foreach ($parents as $p) {
                fputcsv($handle, [
                    $p->nom,
                    $p->prenom,
                    $p->sexe,
                    $p->contact_1,
                    $p->contact_2,
                    $p->email,
                    $p->lien_parental,
                    $p->profession,
                    $p->nationalite,
                ], ';');
            }

            fclose($handle);
        }, 'parents_export_'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * POST /parents/csv/import
     */
    public function import(Request $request)
    {
        $request->validate([
            'fichier' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $request->file('fichier')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->with('error', 'Impossible de lire le fichier CSV.');
        }

        // Détection automatique du séparateur (',' ou ';')
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $header = fgetcsv($handle, 0, $delimiter);
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        $created = 0;
        $skipped = 0;
        $etablissementId = $this->currentEtablissementId();
        $statutId = $this->defaultStatutId();

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (count(array_filter($row)) === 0) {
                    continue; // ligne vide
                }

                $line = array_combine(
                    array_slice($header, 0, count($row)),
                    $row
                );

                $validator = Validator::make($line, [
                    'nom' => ['required', 'string', 'max:100'],
                    'prenom' => ['required', 'string', 'max:100'],
                    'sexe' => ['nullable', 'in:M,F'],
                    'contact_1' => ['required', 'string', 'max:20'],
                    'contact_2' => ['nullable', 'string', 'max:20'],
                    'email' => ['nullable', 'email', 'max:100'],
                    'lien_parental' => ['nullable', 'string', 'max:50'],
                    'profession' => ['nullable', 'string', 'max:100'],
                    'nationalite' => ['nullable', 'string', 'max:50'],
                ]);

                if ($validator->fails()) {
                    $skipped++;
                    continue;
                }

                $clean = $validator->validated();
                $clean['id_etablissement'] = $etablissementId;
                $clean['id_statut'] = $statutId;

                ParentModel::create($clean);
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
            "$created parent(s) importé(s) avec succès".($skipped ? ", $skipped ligne(s) ignorée(s)." : '.')
        );
    }

    /**
     * Validation commune create/update.
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'sexe' => ['nullable', 'in:M,F'],
            'contact_1' => ['required', 'string', 'max:20'],
            'contact_2' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'lien_parental' => ['nullable', 'string', 'max:50'],
            'profession' => ['nullable', 'string', 'max:100'],
            'nationalite' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
