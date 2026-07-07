<?php

namespace App\Http\Controllers;

use App\Models\DossierGed;
use App\Models\DocumentGed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GedController extends Controller
{
    private function etabId()
    {
        return session('etablissement_id', 1);
    }

    public function index()
    {
        $etabId = $this->etabId();

        $dossiers = DossierGed::where('id_etablissement', $etabId)->get();
        $documents = DocumentGed::where('id_etablissement', $etabId)->get();

        return view('ged.index', compact('dossiers', 'documents'));
    }

    // ─── DOSSIERS ───

    public function storeDossier(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'id_parent' => 'nullable|integer',
            'categorie' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $dossier = DossierGed::create([
            'id_etablissement' => $this->etabId(),
            'nom' => $request->nom,
            'id_parent' => $request->id_parent ?: null,
            'categorie' => $request->categorie,
            'description' => $request->description
        ]);

        return response()->json(['success' => true, 'message' => 'Dossier créé.', 'data' => $dossier]);
    }

    public function updateDossier(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string',
            'id_parent' => 'nullable|integer',
            'categorie' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $dossier = DossierGed::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        
        // Prevent setting itself as parent
        if ($request->id_parent == $id) {
            return response()->json(['success' => false, 'message' => 'Un dossier ne peut pas être son propre parent.'], 422);
        }

        $dossier->update([
            'nom' => $request->nom,
            'id_parent' => $request->id_parent ?: null,
            'categorie' => $request->categorie,
            'description' => $request->description
        ]);

        return response()->json(['success' => true, 'message' => 'Dossier mis à jour.', 'data' => $dossier]);
    }

    public function destroyDossier($id)
    {
        $dossier = DossierGed::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        
        // Check if has children folders or documents
        $hasChildren = DossierGed::where('id_parent', $id)->exists();
        $hasDocs = DocumentGed::where('id_dossier', $id)->exists();

        if ($hasChildren || $hasDocs) {
            return response()->json(['success' => false, 'message' => 'Impossible de supprimer un dossier non vide.'], 422);
        }

        $dossier->delete();
        return response()->json(['success' => true, 'message' => 'Dossier supprimé.']);
    }

    // ─── DOCUMENTS ───

    public function storeDocument(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'description' => 'nullable|string',
            'categorie' => 'required|string',
            'id_dossier' => 'nullable|integer',
            'url_fichier' => 'nullable|string',
            'fichier' => 'nullable|file|max:10240' // Max 10MB
        ]);

        $url = $request->url_fichier ?: '';
        $type = 'unknown';
        $taille = 0;

        if ($request->hasFile('fichier')) {
            $file = $request->file('fichier');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/ged'), $filename);
            $url = '/uploads/ged/' . $filename;
            $type = strtolower($file->getClientOriginalExtension());
            $taille = $file->getSize();
        } else if (!empty($url)) {
            $parts = explode('.', split_url_query($url));
            $type = strtolower(end($parts));
        }

        $doc = DocumentGed::create([
            'id_etablissement' => $this->etabId(),
            'id_dossier' => $request->id_dossier ?: null,
            'nom' => $request->nom,
            'description' => $request->description,
            'categorie' => $request->categorie,
            'url_fichier' => $url,
            'type_fichier' => $type,
            'taille_octets' => $taille,
            'uploaded_by' => Auth::id()
        ]);

        return response()->json(['success' => true, 'message' => 'Fichier importé avec succès.', 'data' => $doc]);
    }

    public function updateDocument(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string',
            'description' => 'nullable|string',
            'categorie' => 'required|string',
            'id_dossier' => 'nullable|integer',
            'url_fichier' => 'nullable|string',
            'fichier' => 'nullable|file|max:10240'
        ]);

        $doc = DocumentGed::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();

        $url = $request->url_fichier !== null ? $request->url_fichier : $doc->url_fichier;
        $type = $doc->type_fichier;
        $taille = $doc->taille_octets;

        if ($request->hasFile('fichier')) {
            // Delete old file if exists in uploads
            if (str_starts_with($doc->url_fichier, '/uploads/ged/')) {
                @unlink(public_path(substr($doc->url_fichier, 1)));
            }
            $file = $request->file('fichier');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/ged'), $filename);
            $url = '/uploads/ged/' . $filename;
            $type = strtolower($file->getClientOriginalExtension());
            $taille = $file->getSize();
        } else if ($request->url_fichier !== null && $request->url_fichier !== $doc->url_fichier) {
            $parts = explode('.', split_url_query($url));
            $type = strtolower(end($parts));
        }

        $doc->update([
            'id_dossier' => $request->id_dossier ?: null,
            'nom' => $request->nom,
            'description' => $request->description,
            'categorie' => $request->categorie,
            'url_fichier' => $url,
            'type_fichier' => $type,
            'taille_octets' => $taille
        ]);

        return response()->json(['success' => true, 'message' => 'Document mis à jour.', 'data' => $doc]);
    }

    public function destroyDocument($id)
    {
        $doc = DocumentGed::where('id', $id)->where('id_etablissement', $this->etabId())->firstOrFail();
        
        // Delete physical file if locally hosted
        if (str_starts_with($doc->url_fichier, '/uploads/ged/')) {
            @unlink(public_path(substr($doc->url_fichier, 1)));
        }

        $doc->delete();
        return response()->json(['success' => true, 'message' => 'Document supprimé.']);
    }
}

// Utility to helper parse extensions from URLs
if (!function_exists('split_url_query')) {
    function split_url_query($url) {
        return explode('?', $url)[0];
    }
}
