<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Bourse;
use App\Models\Classe;
use App\Models\Etudiant;
use App\Models\EtudiantBourse;
use App\Models\Filiere;
use App\Models\Inscription;
use App\Models\Niveau;
use App\Models\Statut;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InscriptionController extends Controller
{
    private array $csvColumns = [
        'numero_inscription', 'matricule', 'nom', 'prenom',
        'filiere', 'niveau', 'classe', 'type_inscription', 'date_inscription',
    ];

    /**
     * Statuts de paiement gérés pour une inscription (réutilise la table
     * générique "statut", partagée avec le flag Actif/Inactif des autres
     * modules — aucune colonne dédiée n'existe sur "inscription").
     */
    private array $libellesStatutPaiement = ['En attente de paiement', 'Payé', 'Partiellement payé'];

    private function currentEtablissementId(): ?int
    {
        return session('etablissement_id') ?? auth()->user()->id_etablissement ?? null;
    }

    /**
     * Année scolaire active pour l'établissement courant.
     * Se rabat sur la session si le sélecteur d'année l'y dépose un jour,
     * sinon prend l'année marquée "active" en base.
     */
    private function activeAnnee(): ?AnneeScolaire
    {
        if ($sessionId = session('annee_scolaire_id')) {
            return AnneeScolaire::find($sessionId);
        }

        return AnneeScolaire::where('id_etablissement', $this->currentEtablissementId())
            ->where('active', true)
            ->first();
    }

    private function defaultStatutId(): int
    {
        return Statut::where('libelle', 'Actif')->value('id') ?? 1;
    }

    /**
     * S'assure que les statuts de paiement existent en base (créés une seule
     * fois) et les renvoie dans l'ordre attendu par le formulaire.
     */
    private function statutsPaiement()
    {
        foreach ($this->libellesStatutPaiement as $libelle) {
            Statut::firstOrCreate(['libelle' => $libelle]);
        }

        return Statut::whereIn('libelle', $this->libellesStatutPaiement)
            ->get(['id', 'libelle'])
            ->sortBy(fn ($s) => array_search($s->libelle, $this->libellesStatutPaiement))
            ->values();
    }

    /**
     * GET /inscriptions
     */
    public function index(Request $request)
    {
        $etablissementId = $this->currentEtablissementId();

        $inscriptions = Inscription::query()
            ->with(['etudiant', 'niveau', 'filiere', 'classe'])
            ->when($etablissementId, fn ($q, $id) => $q->where('id_etablissement', $id))
            ->orderByDesc('id')
            ->get();

        $data = $inscriptions->map(fn ($i) => [
            'id' => $i->id,
            'numero_inscription' => $i->numero_inscription,
            'id_etudiant' => $i->id_etudiant,
            'etudiant_nom' => $i->etudiant->nom ?? '',
            'etudiant_prenom' => $i->etudiant->prenom ?? '',
            'matricule' => $i->etudiant->matricule ?? '',
            'id_niveau' => $i->id_niveau,
            'niveau_libelle' => $i->niveau->code ?? ($i->niveau->libelle ?? ''),
            'id_filiere' => $i->id_filiere,
            'filiere_libelle' => $i->filiere->libelle ?? '',
            'id_classe' => $i->id_classe,
            'classe_libelle' => $i->classe->libelle ?? '',
            'type_inscription' => $i->type_inscription ?? '',
            'id_statut' => $i->id_statut,
            'affecte' => (bool) $i->affecte,
            'bourse' => (bool) $i->id_etudiant_bourse,
            'date_inscription' => optional($i->date_inscription)->format('Y-m-d'),
        ]);

        $niveaux = Niveau::query()
            ->when($etablissementId, fn ($q, $id) => $q->where('id_etablissement', $id))
            ->orderBy('ordre')
            ->get(['id', 'libelle', 'code']);

        $filieres = Filiere::query()
            ->when($etablissementId, fn ($q, $id) => $q->where('id_etablissement', $id))
            ->orderBy('libelle')
            ->get(['id', 'libelle']);

        $classes = Classe::query()
            ->when($etablissementId, fn ($q, $id) => $q->where('id_etablissement', $id))
            ->orderBy('libelle')
            ->get(['id', 'libelle', 'id_niveau', 'id_filiere']);

        $etudiants = Etudiant::query()
            ->when($etablissementId, fn ($q, $id) => $q->where('id_etablissement', $id))
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom', 'matricule']);

        $bourses = Bourse::query()
            ->when($etablissementId, fn ($q, $id) => $q->where('id_etablissement', $id))
            ->where('id_statut', $this->defaultStatutId())
            ->orderBy('libelle')
            ->get(['id', 'libelle']);

        return view('inscription.inscription', [
            'inscriptions' => $data,
            'niveaux' => $niveaux,
            'filieres' => $filieres,
            'classes' => $classes,
            'etudiants' => $etudiants,
            'bourses' => $bourses,
            'statutsPaiement' => $this->statutsPaiement(),
            'anneeActive' => $this->activeAnnee(),
        ]);
    }

    /**
     * POST /inscriptions
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $etablissementId = $this->currentEtablissementId();
        $dateInscription = $data['date_inscription'] ?? now()->toDateString();

        $idEtudiantBourse = $this->resolveEtudiantBourse($data, $dateInscription, $etablissementId);

        Inscription::create([
            'numero_inscription' => $this->generateNumeroInscription($dateInscription),
            'date_inscription' => $dateInscription,
            'id_etudiant' => $data['id_etudiant'],
            'id_etudiant_bourse' => $idEtudiantBourse,
            'id_annee_scolaire' => $this->activeAnnee()?->id,
            'id_niveau' => $data['id_niveau'],
            'id_filiere' => $data['id_filiere'],
            'id_classe' => $data['id_classe'],
            'affecte' => $request->boolean('affecte'),
            'type_inscription' => $data['type_inscription'],
            'id_statut' => $data['id_statut'],
            'id_etablissement' => $etablissementId,
        ]);

        return redirect()->route('inscriptions.index')->with('success', 'Inscription ajoutée avec succès.');
    }

    /**
     * PUT/PATCH /inscriptions/{id}
     */
    public function update(Request $request, int $id)
    {
        $inscription = Inscription::findOrFail($id);
        $data = $this->validateData($request);
        $dateInscription = $data['date_inscription'] ?? $inscription->date_inscription;

        $idEtudiantBourse = $this->resolveEtudiantBourse(
            $data,
            $dateInscription,
            $inscription->id_etablissement,
            $inscription
        );

        $inscription->update([
            'id_etudiant' => $data['id_etudiant'],
            'id_etudiant_bourse' => $idEtudiantBourse,
            'id_niveau' => $data['id_niveau'],
            'id_filiere' => $data['id_filiere'],
            'id_classe' => $data['id_classe'],
            'type_inscription' => $data['type_inscription'],
            'id_statut' => $data['id_statut'],
            'date_inscription' => $dateInscription,
            'affecte' => $request->boolean('affecte'),
        ]);

        return redirect()->route('inscriptions.index')->with('success', 'Inscription mise à jour avec succès.');
    }

    /**
     * DELETE /inscriptions/{id}
     */
    public function destroy(int $id)
    {
        Inscription::findOrFail($id)->delete();

        return redirect()->route('inscriptions.index')->with('success', 'Inscription supprimée avec succès.');
    }

    /**
     * POST /inscriptions/etudiants/quick-create
     * Création rapide d'un étudiant depuis le champ "Étudiant" du formulaire
     * (bouton "+"), sans quitter la modale d'inscription.
     */
    public function quickCreateEtudiant(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'sexe' => ['nullable', 'in:M,F'],
            'contact' => ['nullable', 'string', 'max:20'],
        ]);

        $data['id_etablissement'] = $this->currentEtablissementId();
        $data['id_statut'] = $this->defaultStatutId();
        $data['matricule'] = $this->generateMatricule();

        $etudiant = Etudiant::create($data);

        return response()->json([
            'id' => $etudiant->id,
            'nom' => $etudiant->nom,
            'prenom' => $etudiant->prenom,
            'matricule' => $etudiant->matricule,
        ]);
    }

    /**
     * GET /inscriptions/csv/export
     */
    public function export(): StreamedResponse
    {
        $inscriptions = Inscription::query()
            ->with(['etudiant', 'niveau', 'filiere', 'classe'])
            ->when($this->currentEtablissementId(), fn ($q, $id) => $q->where('id_etablissement', $id))
            ->orderByDesc('id')
            ->get();

        return response()->streamDownload(function () use ($inscriptions) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $this->csvColumns, ';');

            foreach ($inscriptions as $i) {
                fputcsv($handle, [
                    $i->numero_inscription,
                    $i->etudiant->matricule ?? '',
                    $i->etudiant->nom ?? '',
                    $i->etudiant->prenom ?? '',
                    $i->filiere->libelle ?? '',
                    $i->niveau->libelle ?? '',
                    $i->classe->libelle ?? '',
                    $i->type_inscription,
                    optional($i->date_inscription)->format('Y-m-d'),
                ], ';');
            }

            fclose($handle);
        }, 'inscriptions_export_'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'id_etudiant' => ['required', 'integer', 'exists:etudiant,id'],
            'id_filiere' => ['required', 'integer', 'exists:filiere,id'],
            'id_niveau' => ['required', 'integer', 'exists:niveau,id'],
            'id_classe' => ['required', 'integer', 'exists:classe,id'],
            'type_inscription' => ['required', 'string', 'max:50'],
            'id_statut' => ['required', 'integer', 'exists:statut,id'],
            'date_inscription' => ['nullable', 'date'],
            'boursier' => ['nullable', 'boolean'],
            'id_bourse' => ['nullable', 'integer', 'exists:bourse,id', 'required_if:boursier,1'],
        ]);
    }

    /**
     * Crée ou met à jour l'enregistrement "etudiant_bourse" lié à l'inscription
     * si l'étudiant est déclaré boursier, sinon détache la bourse existante.
     */
    private function resolveEtudiantBourse(array $data, string $dateInscription, ?int $etablissementId, ?Inscription $inscription = null): ?int
    {
        if (empty($data['boursier']) || empty($data['id_bourse'])) {
            return null;
        }

        $existant = $inscription?->etudiantBourse;

        if ($existant) {
            $existant->update(['id_bourse' => $data['id_bourse']]);

            return $existant->id;
        }

        return EtudiantBourse::create([
            'id_etudiant' => $data['id_etudiant'],
            'id_bourse' => $data['id_bourse'],
            'date_debut' => $dateInscription,
            'date_fin' => null,
            'id_statut' => $this->defaultStatutId(),
            'id_etablissement' => $etablissementId,
        ])->id;
    }

    /**
     * Génère un numéro du type INS + année + numéro séquentiel,
     * cohérent avec le format vu en base (INS-2024-0033).
     */
    private function generateNumeroInscription(string $date): string
    {
        $year = \Carbon\Carbon::parse($date)->format('Y');
        $last = Inscription::where('numero_inscription', 'like', "INS-{$year}-%")
            ->orderByDesc('numero_inscription')
            ->value('numero_inscription');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('INS-%s-%04d', $year, $seq);
    }

    /**
     * Génère un matricule du type MAT + année + numéro séquentiel,
     * identique à EtudiantController::generateMatricule() (création rapide
     * d'un étudiant depuis le formulaire d'inscription).
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
