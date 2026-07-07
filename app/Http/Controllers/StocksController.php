<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Article;
use App\Models\ArticleType;
use App\Models\ArticleVariant;
use App\Models\Classe;
use App\Models\Inscription;
use App\Models\Stock;
use App\Models\StudentArticleRemise;
use App\Traits\ComptabiliteEngineTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StocksController extends Controller
{
    use ComptabiliteEngineTrait;

    private function etabId()
    {
        return session('etablissement_id');
    }

    private function anneeActive()
    {
        return AnneeScolaire::where('id_etablissement', $this->etabId())->where('active', true)->first();
    }

    // ══ PAGE PRINCIPALE ═══════════════════════════════════════════════════════════
    // NB: article_types / articles / article_variants / stocks / student_articles_remise
    // ne sont PAS multi-tenant (aucune colonne id_etablissement exploitée) — cela reproduit
    // fidèlement app_state.dart, qui charge ces entités via _fetch() (global), pas _fetchE().

    public function index()
    {
        $etabId      = $this->etabId();
        $anneeActive = $this->anneeActive();

        $articleTypes = ArticleType::orderBy('libelle_article_types')->get();
        $articles     = Article::orderBy('libelle')->get();
        $variants     = ArticleVariant::get();
        $stocks       = Stock::get();
        $ventes       = StudentArticleRemise::orderByDesc('id')->get();

        $inscriptions = Inscription::with('etudiant')
            ->where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->get();

        $classes = Classe::where('id_etablissement', $etabId)
            ->when($anneeActive, fn ($q) => $q->where('id_annee_scolaire', $anneeActive->id))
            ->orderBy('libelle')->get();

        $statuts = DB::table('statut')->get();

        return view('stocks.index', compact('articleTypes', 'articles', 'variants', 'stocks', 'ventes', 'inscriptions', 'classes', 'statuts'));
    }

    // ══ TYPES D'ARTICLES ══════════════════════════════════════════════════════════

    public function storeType(Request $r)
    {
        $r->validate(['libelle_article_types' => 'required|string', 'slug_article_types' => 'nullable|string']);
        $type = ArticleType::create($r->only('libelle_article_types', 'slug_article_types'));

        return response()->json(['message' => 'Type créé.', 'data' => $type]);
    }

    public function updateType(Request $r, $id)
    {
        $r->validate(['libelle_article_types' => 'required|string', 'slug_article_types' => 'nullable|string']);
        $type = ArticleType::findOrFail($id);
        $type->update($r->only('libelle_article_types', 'slug_article_types'));

        return response()->json(['message' => 'Type modifié.', 'data' => $type]);
    }

    public function destroyType($id)
    {
        $nbArticles = Article::where('article_type_id', $id)->count();
        if ($nbArticles > 0) {
            return response()->json([
                'message' => "$nbArticles article(s) utilisent ce type. Supprimez d'abord ces articles avant de supprimer le type.",
            ], 422);
        }
        ArticleType::where('id', $id)->delete();

        return response()->json(['message' => 'Type supprimé.']);
    }

    // ══ ARTICLES ══════════════════════════════════════════════════════════════════

    public function storeArticle(Request $r)
    {
        $r->validate([
            'libelle' => 'required|string', 'slug' => 'nullable|string', 'description' => 'nullable|string',
            'prix_unitaire' => 'nullable|numeric|min:0', 'article_type_id' => 'required|integer',
        ]);
        $article = Article::create([
            'libelle' => $r->libelle, 'slug' => $r->slug, 'description' => $r->description,
            'prix_unitaire' => $r->prix_unitaire ?: 0, 'article_type_id' => $r->article_type_id,
            'inclus_scolarite' => $r->boolean('inclus_scolarite'),
        ]);

        return response()->json(['message' => 'Article créé.', 'data' => $article]);
    }

    public function updateArticle(Request $r, $id)
    {
        $r->validate([
            'libelle' => 'required|string', 'slug' => 'nullable|string', 'description' => 'nullable|string',
            'prix_unitaire' => 'nullable|numeric|min:0', 'article_type_id' => 'required|integer',
        ]);
        $article = Article::findOrFail($id);
        $article->update([
            'libelle' => $r->libelle, 'slug' => $r->slug, 'description' => $r->description,
            'prix_unitaire' => $r->prix_unitaire ?: 0, 'article_type_id' => $r->article_type_id,
            'inclus_scolarite' => $r->boolean('inclus_scolarite'),
        ]);

        return response()->json(['message' => 'Article modifié.', 'data' => $article]);
    }

    public function destroyArticle($id)
    {
        $variantIds = ArticleVariant::where('article_id', $id)->pluck('id');
        StudentArticleRemise::whereIn('article_variant_id', $variantIds)->delete();
        Stock::whereIn('article_variant_id', $variantIds)->delete();
        ArticleVariant::where('article_id', $id)->delete();
        Article::where('id', $id)->delete();

        return response()->json(['message' => 'Article supprimé.']);
    }

    // ══ VARIANTES ═════════════════════════════════════════════════════════════════

    public function storeVariant(Request $r)
    {
        $r->validate(['article_id' => 'required|integer', 'taille' => 'nullable|string', 'couleur' => 'nullable|string', 'reference' => 'nullable|string']);
        $variant = ArticleVariant::create($r->only('article_id', 'taille', 'couleur', 'reference'));

        return response()->json(['message' => 'Variante créée.', 'data' => $variant]);
    }

    public function updateVariant(Request $r, $id)
    {
        $r->validate(['taille' => 'nullable|string', 'couleur' => 'nullable|string', 'reference' => 'nullable|string']);
        $variant = ArticleVariant::findOrFail($id);
        $variant->update($r->only('taille', 'couleur', 'reference'));

        return response()->json(['message' => 'Variante modifiée.', 'data' => $variant]);
    }

    public function destroyVariant($id)
    {
        StudentArticleRemise::where('article_variant_id', $id)->delete();
        Stock::where('article_variant_id', $id)->delete();
        ArticleVariant::where('id', $id)->delete();

        return response()->json(['message' => 'Variante supprimée.']);
    }

    // ══ ÉTAT DU STOCK ═════════════════════════════════════════════════════════════

    public function adjustStock(Request $r)
    {
        $r->validate(['article_variant_id' => 'required|integer', 'quantite' => 'required|integer']);
        $etabId  = $this->etabId();
        $variant = ArticleVariant::with('article')->findOrFail($r->article_variant_id);
        $stock   = Stock::where('article_variant_id', $r->article_variant_id)->first();
        $oldQty  = $stock->quantite ?? 0;
        $newQty  = (int) $r->quantite;

        if ($stock) {
            $stock->update(['quantite' => $newQty]);
        } else {
            $stock = Stock::create(['article_variant_id' => $r->article_variant_id, 'quantite' => $newQty]);
        }

        $delta = $newQty - $oldQty;
        if ($delta > 0) {
            $variantePart = collect([$variant->taille, $variant->couleur])->filter()->implode('/');
            $this->createStockOperationInterne(
                $etabId,
                'Sortie',
                'Ajustement stock - '.($variant->article?->libelle ?? '—').($variantePart ? " ($variantePart)" : '').' : +'.$delta.' unite(s)',
                0,
                'Achats Stock Articles'
            );
        }

        return response()->json(['message' => "Stock mis à jour : $newQty unités", 'data' => $stock]);
    }

    public function reapproStock(Request $r)
    {
        $r->validate(['article_variant_id' => 'required|integer', 'ajout' => 'required|integer|min:1', 'prix_achat' => 'nullable|numeric|min:0']);
        $etabId  = $this->etabId();
        $variant = ArticleVariant::with('article')->findOrFail($r->article_variant_id);
        $stock   = Stock::where('article_variant_id', $r->article_variant_id)->first();
        $oldQty  = $stock->quantite ?? 0;
        $ajout   = (int) $r->ajout;
        $newQty  = $oldQty + $ajout;
        $prixAchat = (float) ($r->prix_achat ?: 0);

        $payload = ['article_variant_id' => $r->article_variant_id, 'quantite' => $newQty];
        if ($prixAchat > 0) {
            $payload['montant_achat'] = $prixAchat * $ajout;
            $payload['prix_unitaire'] = $prixAchat;
        }
        if ($stock) {
            $stock->update($payload);
        } else {
            $stock = Stock::create($payload);
        }

        $montantAchat = $prixAchat > 0 ? $prixAchat * $ajout : 0;
        $libelleAchat = 'Réappro. stock — '.($variant->article?->libelle ?? '—')
            .($variant->taille ? ' / '.$variant->taille : '')
            .($variant->couleur ? ' / '.$variant->couleur : '')
            ." ($ajout unité(s))";

        $this->createStockOperationInterne($etabId, 'Sortie', $libelleAchat, $montantAchat, 'Achats Stock Articles');
        if ($montantAchat > 0) {
            $this->onStockAchatInterne($etabId, $montantAchat, $libelleAchat);
        }

        return response()->json(['message' => "+$ajout unités ajoutées → $newQty en stock", 'data' => $stock]);
    }

    public function destroyStock($id)
    {
        Stock::where('id', $id)->delete();

        return response()->json(['message' => 'Ligne de stock supprimée.']);
    }

    // ══ VENTES ÉTUDIANTS ══════════════════════════════════════════════════════════

    public function storeVente(Request $r)
    {
        $r->validate([
            'inscription_student_id' => 'required|integer', 'article_variant_id' => 'required|integer',
            'quantite' => 'required|integer|min:1', 'prix_unitaire' => 'nullable|numeric|min:0',
            'montant' => 'nullable|numeric|min:0', 'statut_paiement' => 'nullable|integer',
        ]);
        $etabId = $this->etabId();
        $qty    = (int) $r->quantite;

        $vente = StudentArticleRemise::create([
            'inscription_student_id' => $r->inscription_student_id,
            'article_variant_id'     => $r->article_variant_id,
            'quantite'               => $qty,
            'prix_unitaire'          => $r->prix_unitaire ?: 0,
            'montant'                => $r->montant ?: 0,
            'statut_paiement'        => $r->statut_paiement ?: null,
        ]);

        $stock = Stock::where('article_variant_id', $r->article_variant_id)->first();
        if ($stock) {
            $newQty = max(0, $stock->quantite - $qty);
            $stock->update(['quantite' => $newQty]);
        }

        $montantVente = (float) ($r->montant ?: 0);
        if ($montantVente > 0) {
            $variant = ArticleVariant::with('article')->find($r->article_variant_id);
            $label   = ($variant?->article?->libelle ?? '—').($variant ? ' — '.trim(($variant->taille ?: '').' '.($variant->couleur ?: '')) : '');
            $libelleVente = 'Vente — '.trim($label, ' —').' × '.$qty;
            $this->createStockOperationInterne($etabId, 'Entrée', $libelleVente, $montantVente, 'Ventes Articles');
            $this->onStockVenteInterne($etabId, $montantVente, $libelleVente);
        }

        return response()->json(['message' => 'Vente enregistrée.', 'data' => $vente]);
    }

    public function updateVente(Request $r, $id)
    {
        $r->validate([
            'inscription_student_id' => 'required|integer', 'article_variant_id' => 'required|integer',
            'quantite' => 'required|integer|min:1', 'prix_unitaire' => 'nullable|numeric|min:0',
            'montant' => 'nullable|numeric|min:0', 'statut_paiement' => 'nullable|integer',
        ]);
        $vente = StudentArticleRemise::findOrFail($id);
        $vente->update([
            'inscription_student_id' => $r->inscription_student_id,
            'article_variant_id'     => $r->article_variant_id,
            'quantite'               => $r->quantite,
            'prix_unitaire'          => $r->prix_unitaire ?: 0,
            'montant'                => $r->montant ?: 0,
            'statut_paiement'        => $r->statut_paiement ?: null,
        ]);

        return response()->json(['message' => 'Vente modifiée.', 'data' => $vente]);
    }

    public function destroyVente($id)
    {
        StudentArticleRemise::where('id', $id)->delete();

        return response()->json(['message' => 'Vente supprimée.']);
    }

    // ══ DISTRIBUTION SCOLARITÉ ════════════════════════════════════════════════════

    public function toggleRecuperation(Request $r)
    {
        $r->validate(['inscription_student_id' => 'required|integer', 'article_variant_id' => 'required|integer']);
        $existing = StudentArticleRemise::where('inscription_student_id', $r->inscription_student_id)
            ->where('article_variant_id', $r->article_variant_id)->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['message' => 'Récupération annulée.', 'recupere' => false]);
        }

        $remise = StudentArticleRemise::create([
            'inscription_student_id' => $r->inscription_student_id,
            'article_variant_id'     => $r->article_variant_id,
            'quantite'               => 1,
            'montant'                => 0,
            'statut_paiement'        => null,
        ]);

        return response()->json(['message' => 'Marqué comme récupéré.', 'recupere' => true, 'data' => $remise]);
    }

    public function marquerTous(Request $r)
    {
        $r->validate(['article_variant_id' => 'required|integer', 'inscription_ids' => 'required|array']);
        $existants = StudentArticleRemise::where('article_variant_id', $r->article_variant_id)
            ->whereIn('inscription_student_id', $r->inscription_ids)->pluck('inscription_student_id')->all();

        $count = 0;
        foreach ($r->inscription_ids as $insId) {
            if (in_array($insId, $existants)) {
                continue;
            }
            StudentArticleRemise::create([
                'inscription_student_id' => $insId,
                'article_variant_id'     => $r->article_variant_id,
                'quantite'               => 1,
                'montant'                => 0,
                'statut_paiement'        => null,
            ]);
            $count++;
        }

        return response()->json(['message' => "$count étudiant(s) marqué(s).", 'count' => $count]);
    }
}
