<x-app-layout title="Gestion de Stock">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none}
.f-select{width:100%;padding:10px 12px;background:#fff;border:1px solid #E2E8F0;border-radius:8px;font-size:13px;color:#1E293B;outline:none}
.f-badge{display:inline-flex;padding:3px 9px;border-radius:8px;font-size:11px;font-weight:700}
.kpi-card{flex:1;background:#fff;border-radius:12px;border:1px solid #E2E8F0;box-shadow:0 1px 3px rgba(0,0,0,.02);padding:12px 16px;display:flex;align-items:center;gap:12px}
.kpi-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stk-tabbar{background:#fff;border-radius:12px;border:1px solid #E2E8F0;box-shadow:0 1px 3px rgba(0,0,0,.02);display:flex}
.stk-tab{flex:1;padding:12px;text-align:center;font-size:13px;font-weight:600;color:#94A3B8;background:transparent;border:none;cursor:pointer;border-bottom:3px solid transparent}
.stk-tab.active{color:#5A67D8;border-bottom-color:#5A67D8;font-weight:700}
.stk-tab i{margin-right:6px}
.ss-drop{position:absolute;top:100%;left:0;right:0;margin-top:4px;max-height:220px;overflow-y:auto;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.12);border:1px solid #E2E8F0;z-index:30}
.ss-item{padding:9px 14px;font-size:13px;color:#334155;cursor:pointer}
.ss-item:hover{background:#F1F5F9}
.ss-sel{background:#EEF2FF;color:#5A67D8;font-weight:600}
.f-table-container{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #E2E8F0;overflow:hidden}
.f-table-header th{padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.06em;background:#F8FAFC;border-bottom:1px solid #E2E8F0}
.f-table-row td{padding:12px 16px;font-size:13px;color:#334155;border-bottom:1px solid #F1F5F9;vertical-align:middle}
.f-table-row:hover{background:#F8FAFC}
.type-item{padding:10px 12px;display:flex;align-items:center;gap:10px;cursor:pointer;border-bottom:1px solid #F1F5F9}
.type-item:last-child{border-bottom:none}
.type-item.selected{background:rgba(90,103,216,.06)}
.article-card{background:#F8FAFC;border-radius:10px;border:1px solid #E2E8F0;margin-bottom:6px;overflow:hidden}
.article-card.expanded{border-color:rgba(90,103,216,.4)}
.variant-row{background:#fff;border:1px solid #E2E8F0;border-radius:8px;padding:8px 12px;margin-bottom:6px;display:flex;align-items:center;gap:8px}
.variant-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:10px;font-size:11px;font-weight:600}
.mode-btn{padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1px solid #E2E8F0;background:#fff;color:#64748B;cursor:pointer;display:inline-flex;align-items:center;gap:7px}
.mode-btn.active{background:#5A67D8;border-color:#5A67D8;color:#fff}
.vente-card{background:#fff;border-radius:12px;box-shadow:0 1px 8px rgba(0,0,0,.03);border:1px solid #F1F5F9;padding:12px 16px;margin-bottom:10px;display:flex;align-items:center;gap:14px}
.act-btn{width:30px;height:30px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;border:none;background:transparent;cursor:pointer}
</style>
@endpush

@php
$typesJson = $articleTypes->map(fn($t) => ['id'=>$t->id,'libelle_article_types'=>$t->libelle_article_types,'slug_article_types'=>$t->slug_article_types]);
$articlesJson = $articles->map(fn($a) => ['id'=>$a->id,'libelle'=>$a->libelle,'slug'=>$a->slug,'description'=>$a->description,'prix_unitaire'=>$a->prix_unitaire,'article_type_id'=>$a->article_type_id,'inclus_scolarite'=>$a->inclus_scolarite]);
$variantsJson = $variants->map(fn($v) => ['id'=>$v->id,'article_id'=>$v->article_id,'taille'=>$v->taille,'couleur'=>$v->couleur,'reference'=>$v->reference]);
$stocksJson = $stocks->map(fn($s) => ['id'=>$s->id,'article_variant_id'=>$s->article_variant_id,'quantite'=>$s->quantite,'montant_achat'=>$s->montant_achat,'prix_unitaire'=>$s->prix_unitaire]);
$ventesJson = $ventes->map(fn($v) => ['id'=>$v->id,'inscription_student_id'=>$v->inscription_student_id,'article_variant_id'=>$v->article_variant_id,'quantite'=>$v->quantite,'prix_unitaire'=>$v->prix_unitaire,'montant'=>$v->montant,'statut_paiement'=>$v->statut_paiement,'created_at'=>$v->created_at ? substr((string)$v->created_at,0,10) : null]);
$inscriptionsJson = $inscriptions->map(fn($i) => ['id'=>$i->id,'id_etudiant'=>$i->id_etudiant,'etu_nom'=>$i->etudiant?->nom??'?','etu_prenom'=>$i->etudiant?->prenom??'','etu_matricule'=>$i->etudiant?->matricule??'—','id_classe'=>$i->id_classe]);
$classesJson = $classes->map(fn($c) => ['id'=>$c->id,'libelle'=>$c->libelle]);
$statutsJson = collect($statuts)->map(fn($s) => ['id'=>$s->id,'libelle'=>$s->libelle]);
@endphp

<div x-data="stocksPage({{ $typesJson }}, {{ $articlesJson }}, {{ $variantsJson }}, {{ $stocksJson }}, {{ $ventesJson }}, {{ $inscriptionsJson }}, {{ $classesJson }}, {{ $statutsJson }})" class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Gestion de Stock</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="articles.length"></span> article(s) · <span x-text="variants.length"></span> variante(s)</p>
        </div>
        <button @click="location.reload()" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border" style="color:#64748B;border-color:#E2E8F0"><i class="ri-refresh-line"></i> Actualiser</button>
    </div>

    {{-- KPIs --}}
    <div class="flex gap-3 flex-wrap">
        <div class="kpi-card"><div class="kpi-icon" style="background:rgba(90,103,216,.1)"><i class="ri-shape-line" style="color:#5A67D8"></i></div><div><p class="text-lg font-bold" style="color:#5A67D8" x-text="articleTypes.length"></p><p class="text-[11px]" style="color:#94A3B8">Types d'Articles</p></div></div>
        <div class="kpi-card"><div class="kpi-icon" style="background:rgba(90,103,216,.1)"><i class="ri-archive-2-line" style="color:#5A67D8"></i></div><div><p class="text-lg font-bold" style="color:#5A67D8" x-text="articles.length"></p><p class="text-[11px]" style="color:#94A3B8">Articles</p></div></div>
        <div class="kpi-card"><div class="kpi-icon" style="background:rgba(13,148,136,.1)"><i class="ri-equalizer-line" style="color:#0d9488"></i></div><div><p class="text-lg font-bold" style="color:#0d9488" x-text="variants.length"></p><p class="text-[11px]" style="color:#94A3B8">Variantes</p></div></div>
        <div class="kpi-card"><div class="kpi-icon" :style="'background:'+(outOfStockCount()>0?'rgba(239,68,68,.1)':'rgba(22,163,74,.1)')"><i class="ri-error-warning-line" :style="'color:'+(outOfStockCount()>0?'#ef4444':'#16a34a')"></i></div><div><p class="text-lg font-bold" :style="'color:'+(outOfStockCount()>0?'#ef4444':'#16a34a')" x-text="outOfStockCount()"></p><p class="text-[11px]" style="color:#94A3B8">En Rupture</p></div></div>
    </div>

    {{-- Tab bar --}}
    <div class="stk-tabbar">
        <button class="stk-tab" :class="tab===0?'active':''" @click="tab=0"><i class="ri-archive-2-line"></i>Articles &amp; Types</button>
        <button class="stk-tab" :class="tab===1?'active':''" @click="tab=1"><i class="ri-store-2-line"></i>État du Stock</button>
        <button class="stk-tab" :class="tab===2?'active':''" @click="tab=2"><i class="ri-shopping-bag-3-line"></i>Ventes Étudiants</button>
    </div>

    {{-- ═══════════ TAB 0 : ARTICLES & TYPES ═══════════ --}}
    <template x-if="tab===0">
        <div class="flex gap-4 items-start">
            {{-- Panneau gauche : Types --}}
            <div style="width:260px" class="flex-shrink-0">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-bold" style="color:#1E293B">Types d'Articles</p>
                    <button @click="openTypeCreate()" class="text-xs font-semibold flex items-center gap-1" style="color:#5A67D8"><i class="ri-add-line"></i> Ajouter</button>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <template x-for="t in articleTypes" :key="t.id">
                        <div class="type-item" :class="filterType===t.id?'selected':''" @click="filterType = filterType===t.id ? '' : t.id">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :style="filterType===t.id?'background:rgba(90,103,216,.15)':'background:#F1F5F9'">
                                <i class="ri-shape-line" :style="'color:'+(filterType===t.id?'#5A67D8':'#94A3B8')" style="font-size:14px"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="truncate" :style="filterType===t.id?'color:#5A67D8;font-weight:700':'color:#334155'" style="font-size:13px" x-text="t.libelle_article_types"></p>
                                <p style="font-size:11px;color:#94A3B8" x-text="articles.filter(a=>a.article_type_id===t.id).length+' article(s)'"></p>
                            </div>
                            <button @click.stop="openTypeEdit(t)" class="act-btn hover:bg-blue-50" style="color:#3b82f6;width:26px;height:26px"><i class="ri-edit-2-line text-xs"></i></button>
                            <button @click.stop="deleteType(t)" class="act-btn hover:bg-red-50" style="color:#ef4444;width:26px;height:26px"><i class="ri-delete-bin-2-line text-xs"></i></button>
                        </div>
                    </template>
                    <p x-show="!articleTypes.length" class="text-center py-8 text-sm" style="color:#94A3B8">Aucun type</p>
                </div>
            </div>

            {{-- Panneau droit : Articles + Variantes --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-bold" style="color:#1E293B" x-text="filterType ? filteredArticles().length+' article(s) — '+typeName(filterType) : filteredArticles().length+' article(s) au total'"></p>
                    <div class="flex items-center gap-2">
                        <button x-show="filterType||searchArticles" @click="filterType='';searchArticles=''" class="text-xs font-semibold" style="color:#f87171"><i class="ri-close-line"></i> Effacer</button>
                        <button @click="openArticleCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90" style="background:#5A67D8"><i class="ri-add-line"></i> Nouvel Article</button>
                    </div>
                </div>
                <div class="relative mb-3">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-300"></i>
                    <input x-model="searchArticles" type="text" placeholder="Rechercher un article..." class="pl-9 pr-4 py-2 w-full rounded-lg text-sm border border-slate-200 bg-white outline-none">
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-2">
                    <template x-for="art in filteredArticles()" :key="art.id">
                        <div class="article-card" :class="expandedId===art.id?'expanded':''">
                            <div class="flex items-center gap-3 px-3.5 py-2.5 cursor-pointer" @click="expandedId = expandedId===art.id ? null : art.id">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(90,103,216,.1)"><i class="ri-archive-2-line" style="color:#5A67D8"></i></div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm truncate" style="color:#1E293B" x-text="art.libelle"></p>
                                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                                        <span class="f-badge" style="background:rgba(90,103,216,.1);color:#5A67D8" x-text="typeName(art.article_type_id)"></span>
                                        <span style="font-size:12px;color:#5A67D8;font-weight:600" x-text="fmt(art.prix_unitaire)+' FCFA'"></span>
                                        <span x-html="stockChipHtml(totalStockForArticle(art.id))"></span>
                                    </div>
                                </div>
                                <button @click.stop="openArticleEdit(art)" class="act-btn hover:bg-blue-50" style="color:#3b82f6"><i class="ri-edit-2-line text-[15px]"></i></button>
                                <button @click.stop="deleteArticle(art)" class="act-btn hover:bg-red-50" style="color:#ef4444"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                                <i :class="expandedId===art.id?'ri-arrow-up-s-line':'ri-arrow-down-s-line'" style="color:#94A3B8;font-size:20px"></i>
                            </div>
                            <template x-if="expandedId===art.id">
                                <div style="border-top:1px solid #E2E8F0" class="p-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold" style="color:#94A3B8" x-text="variantsFor(art.id).length+' variante(s)'"></span>
                                        <button @click="openVariantCreate(art.id)" class="text-xs font-semibold" style="color:#5A67D8"><i class="ri-add-line"></i> Ajouter variante</button>
                                    </div>
                                    <p x-show="!variantsFor(art.id).length" class="text-xs italic py-2" style="color:#CBD5E1">Aucune variante définie</p>
                                    <template x-for="v in variantsFor(art.id)" :key="v.id">
                                        <div class="variant-row">
                                            <div class="flex-1 flex items-center gap-1.5 flex-wrap">
                                                <span x-show="v.taille" class="variant-chip" style="background:rgba(13,148,136,.08);color:#0d9488"><i class="ri-ruler-line"></i><span x-text="v.taille"></span></span>
                                                <span x-show="v.couleur" class="variant-chip" style="background:rgba(147,51,234,.08);color:#9333ea"><i class="ri-palette-line"></i><span x-text="v.couleur"></span></span>
                                                <span x-show="v.reference" style="font-size:11px;color:#94A3B8;font-style:italic" x-text="'#'+v.reference"></span>
                                            </div>
                                            <span x-html="stockChipHtml(stockQty(v.id))"></span>
                                            <button @click="openVariantEdit(art.id, v)" class="act-btn hover:bg-blue-50" style="color:#3b82f6"><i class="ri-edit-2-line text-[15px]"></i></button>
                                            <button @click="deleteVariant(v)" class="act-btn hover:bg-red-50" style="color:#ef4444"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                    <p x-show="!filteredArticles().length" class="text-center py-12 text-sm" style="color:#94A3B8">Aucun article trouvé</p>
                </div>
            </div>
        </div>
    </template>

    {{-- ═══════════ TAB 1 : ÉTAT DU STOCK ═══════════ --}}
    <template x-if="tab===1">
        <div class="space-y-4">
            <div class="flex gap-3 flex-wrap">
                <div class="kpi-card"><div class="kpi-icon" style="background:rgba(90,103,216,.1)"><i class="ri-equalizer-line" style="color:#5A67D8"></i></div><div><p class="text-lg font-bold" style="color:#5A67D8" x-text="stockRows().length"></p><p class="text-[11px]" style="color:#94A3B8">Total Variantes</p></div></div>
                <div class="kpi-card"><div class="kpi-icon" style="background:rgba(249,115,22,.1)"><i class="ri-alert-line" style="color:#f97316"></i></div><div><p class="text-lg font-bold" style="color:#f97316" x-text="stockRows().filter(r=>r.quantite<5).length"></p><p class="text-[11px]" style="color:#94A3B8">Stock Faible (&lt;5)</p></div></div>
                <div class="kpi-card"><div class="kpi-icon" style="background:rgba(239,68,68,.1)"><i class="ri-error-warning-line" style="color:#ef4444"></i></div><div><p class="text-lg font-bold" style="color:#ef4444" x-text="stockRows().filter(r=>r.quantite<=0).length"></p><p class="text-[11px]" style="color:#94A3B8">Rupture de Stock</p></div></div>
                <div class="kpi-card"><div class="kpi-icon" style="background:rgba(22,163,74,.1)"><i class="ri-archive-2-line" style="color:#16a34a"></i></div><div><p class="text-lg font-bold" style="color:#16a34a" x-text="stockRows().reduce((s,r)=>s+r.quantite,0)"></p><p class="text-[11px]" style="color:#94A3B8">Total Unités</p></div></div>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative" style="width:300px">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-300"></i>
                    <input x-model="searchStock" type="text" placeholder="Rechercher..." class="pl-9 pr-4 py-2 w-full rounded-lg text-sm border border-slate-200 bg-white outline-none">
                </div>
                <select x-model="filterArticleStock" class="f-select" style="width:220px">
                    <option value="">Tous les articles</option>
                    <template x-for="a in articles" :key="a.id"><option :value="a.id" x-text="a.libelle"></option></template>
                </select>
            </div>
            <div class="f-table-container">
                <table class="w-full">
                    <thead class="f-table-header"><tr><th>Article</th><th>Taille</th><th>Couleur</th><th>Référence</th><th>En Stock</th><th>Statut</th><th style="text-align:right">Actions</th></tr></thead>
                    <tbody>
                        <template x-for="r in stockRows()" :key="r.variantId">
                            <tr class="f-table-row">
                                <td class="font-bold" x-text="r.articleLabel"></td>
                                <td><span x-show="r.taille" class="f-badge" style="background:rgba(13,148,136,.1);color:#0d9488" x-text="r.taille"></span><span x-show="!r.taille" style="color:#94A3B8">—</span></td>
                                <td x-text="r.couleur||'—'" :style="!r.couleur?'color:#94A3B8':''"></td>
                                <td x-show="r.reference" style="font-size:12px;color:#94A3B8;font-style:italic" x-text="'#'+r.reference"></td>
                                <td x-show="!r.reference" style="color:#94A3B8">—</td>
                                <td class="font-bold" style="font-size:16px" :style="'color:'+(r.quantite<=0?'#ef4444':r.quantite<5?'#f97316':'#16a34a')" x-text="r.quantite"></td>
                                <td>
                                    <span class="f-badge" :style="r.quantite<=0?'background:rgba(239,68,68,.1);color:#ef4444':(r.quantite<5?'background:rgba(249,115,22,.1);color:#f97316':'background:rgba(22,163,74,.1);color:#16a34a')"
                                          x-text="r.quantite<=0?'Rupture':(r.quantite<5?'Faible':'OK')"></span>
                                </td>
                                <td style="text-align:right">
                                    <button @click="openReappro(r)" class="act-btn hover:bg-green-50" style="color:#16a34a"><i class="ri-add-circle-line text-[15px]"></i></button>
                                    <button @click="openAdjust(r)" class="act-btn hover:bg-blue-50" style="color:#3b82f6"><i class="ri-edit-2-line text-[15px]"></i></button>
                                    <button @click="deleteStock(r)" class="act-btn hover:bg-red-50" style="color:#ef4444"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <p x-show="!stockRows().length" class="text-center py-10 text-sm" style="color:#94A3B8">Aucune donnée</p>
            </div>
        </div>
    </template>

    {{-- ═══════════ TAB 2 : VENTES ÉTUDIANTS ═══════════ --}}
    <template x-if="tab===2">
        <div class="space-y-4">
            <div class="flex gap-2">
                <button class="mode-btn" :class="!modeDistribution?'active':''" @click="modeDistribution=false"><i class="ri-shopping-bag-3-line"></i> Ventes</button>
                <button class="mode-btn" :class="modeDistribution?'active':''" @click="modeDistribution=true"><i class="ri-checkbox-multiple-line"></i> Distribution Scolarité</button>
            </div>

            {{-- Vue Ventes --}}
            <template x-if="!modeDistribution">
                <div class="space-y-4">
                    <div class="flex gap-3 flex-wrap">
                        <div class="kpi-card"><div class="kpi-icon" style="background:rgba(90,103,216,.1)"><i class="ri-shopping-bag-line" style="color:#5A67D8"></i></div><div><p class="text-lg font-bold" style="color:#5A67D8" x-text="ventes.length"></p><p class="text-[11px]" style="color:#94A3B8">Total Ventes</p></div></div>
                        <div class="kpi-card"><div class="kpi-icon" style="background:rgba(90,103,216,.1)"><i class="ri-bank-card-line" style="color:#5A67D8"></i></div><div><p class="text-lg font-bold" style="color:#5A67D8" x-text="ventes.filter(v=>Number(v.montant)>0).length"></p><p class="text-[11px]" style="color:#94A3B8">Payantes</p><p class="text-[11px]" style="color:#5A67D8" x-text="fmt(ventes.reduce((s,v)=>s+Number(v.montant||0),0))+' FCFA'"></p></div></div>
                        <div class="kpi-card" style="background:#f0fdfa;border-color:#99f6e4"><div class="kpi-icon" style="background:rgba(13,148,136,.15)"><i class="ri-school-line" style="color:#0d9488"></i></div><div><p class="text-lg font-bold" style="color:#0f766e" x-text="ventes.filter(v=>Number(v.montant)===0&&(!v.prix_unitaire||Number(v.prix_unitaire)===0)).length"></p><p class="text-[11px]" style="color:#0f766e">Inclus Scolarité</p><p class="text-[11px]" style="color:#0f766e">0 FCFA facturé</p></div></div>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <select x-model="filterStatutVentes" class="f-select" style="width:200px">
                            <option value="">Tous les statuts</option>
                            <template x-for="s in statuts" :key="s.id"><option :value="s.id" x-text="s.libelle"></option></template>
                        </select>
                        <button x-show="filterStatutVentes||searchVentes" @click="filterStatutVentes='';searchVentes=''" class="text-xs font-semibold" style="color:#f87171"><i class="ri-close-line"></i> Effacer</button>
                        <div class="flex-1"></div>
                        <span x-show="filteredVentes().length" class="f-badge" style="background:rgba(90,103,216,.08);color:#5A67D8;border:1px solid rgba(90,103,216,.3)" x-text="'Total : '+fmt(filteredVentes().reduce((s,v)=>s+Number(v.montant||0),0))+' FCFA'"></span>
                        <button @click="openVenteCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90" style="background:#5A67D8"><i class="ri-add-shopping-cart-line"></i> Nouvelle Vente</button>
                    </div>
                    <div class="relative">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-300"></i>
                        <input x-model="searchVentes" type="text" placeholder="Rechercher un étudiant ou un article..." class="pl-9 pr-4 py-2 w-full rounded-lg text-sm border border-slate-200 bg-white outline-none">
                    </div>
                    <div>
                        <template x-for="v in filteredVentes()" :key="v.id">
                            <div class="vente-card">
                                <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(90,103,216,.1)"><i class="ri-user-line" style="color:#5A67D8"></i></div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm" style="color:#1E293B" x-text="studentLabel(v.inscription_student_id)"></p>
                                    <p style="font-size:12px;color:#64748B" x-text="variantLabel(v.article_variant_id)"></p>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span style="font-size:11px;color:#94A3B8"><i class="ri-shopping-bag-line"></i> Qté: <span x-text="v.quantite"></span></span>
                                        <span style="font-size:11px;color:#94A3B8"><i class="ri-calendar-line"></i> <span x-text="v.created_at||'—'"></span></span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1.5">
                                    <template x-if="isInclus(v)">
                                        <span style="font-size:13px;font-weight:700;color:#0f766e"><i class="ri-school-line"></i> Inclus — 0 F</span>
                                    </template>
                                    <template x-if="!isInclus(v)">
                                        <span style="font-size:15px;font-weight:700;color:#5A67D8" x-text="fmt(v.montant)+' FCFA'"></span>
                                    </template>
                                    <span x-show="isInclus(v)" class="f-badge" style="background:rgba(13,148,136,.08);color:#0d9488;border:1px solid rgba(13,148,136,.3)"><i class="ri-school-line"></i> Inclus scolarité</span>
                                    <span x-show="!isInclus(v)" class="f-badge" :style="statutColorStyle(v.statut_paiement)" x-text="statutLabel(v.statut_paiement)"></span>
                                    <div class="flex gap-1">
                                        <button @click="openVenteEdit(v)" class="act-btn hover:bg-blue-50" style="color:#3b82f6"><i class="ri-edit-2-line text-[15px]"></i></button>
                                        <button @click="deleteVente(v)" class="act-btn hover:bg-red-50" style="color:#ef4444"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="!filteredVentes().length" class="text-center py-14">
                            <i class="ri-shopping-cart-2-line" style="font-size:56px;color:#CBD5E1"></i>
                            <p class="mt-3 text-sm" style="color:#94A3B8">Aucune vente trouvée</p>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Vue Distribution --}}
            <template x-if="modeDistribution">
                <div class="space-y-3">
                    <div class="flex gap-3">
                        <div style="flex:3" class="bg-white rounded-lg border border-slate-200 px-3 py-1.5">
                            <label class="text-[10px] font-bold" style="color:#94A3B8">ARTICLE (INCLUS SCOLARITÉ)</label>
                            <select x-model.number="distVariantId" class="w-full text-sm outline-none border-none">
                                <option value="">Choisir un article…</option>
                                <template x-for="v in inclusVariants()" :key="v.id"><option :value="v.id" x-text="variantLabel(v.id)"></option></template>
                            </select>
                        </div>
                        <div style="flex:2" class="bg-white rounded-lg border border-slate-200 px-3 py-1.5">
                            <label class="text-[10px] font-bold" style="color:#94A3B8">CLASSE</label>
                            <select x-model.number="distFilterClasse" class="w-full text-sm outline-none border-none">
                                <option value="">Toutes les classes</option>
                                <template x-for="c in classes" :key="c.id"><option :value="c.id" x-text="c.libelle"></option></template>
                            </select>
                        </div>
                    </div>

                    <div x-show="!distVariantId" class="flex items-center gap-2 p-3 rounded-lg" style="background:#fff7ed;border:1px solid #fed7aa">
                        <i class="ri-information-line" style="color:#c2410c"></i>
                        <span style="font-size:12px;color:#9a3412">Sélectionnez un article pour activer les boutons de distribution</span>
                    </div>

                    <div x-show="distVariantId" class="bg-white rounded-xl border border-slate-200 p-3.5">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <span class="text-sm font-semibold flex items-center gap-2" style="color:#0f766e"><i class="ri-checkbox-circle-line"></i> <span x-text="distRecuperes()+' / '+distInscriptions().length+' étudiant(s) ont récupéré'"></span></span>
                            <div class="flex items-center gap-3">
                                <input x-model="distSearch" type="text" placeholder="Rechercher un étudiant…" class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 outline-none" style="width:200px">
                                <button x-show="distRecuperes()<distInscriptions().length" @click="marquerTous()" class="px-3.5 py-2 rounded-lg text-white text-sm font-semibold" style="background:#0d9488"><i class="ri-checkbox-multiple-line"></i> Tout marquer</button>
                            </div>
                        </div>
                        <div class="mt-2.5 bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="h-full rounded-full" style="background:#0d9488" :style="'width:'+(distInscriptions().length?(distRecuperes()/distInscriptions().length*100):0)+'%'"></div>
                        </div>
                        <p x-show="distInscriptions().length" class="text-xs mt-1" style="color:#0d9488" x-text="(distInscriptions().length?(distRecuperes()/distInscriptions().length*100).toFixed(1):0)+' % récupéré'"></p>
                    </div>

                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <template x-for="ins in distInscriptions()" :key="ins.id">
                            <div class="flex items-center gap-3 px-4 py-2" style="border-bottom:1px solid #F8FAFC">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" :style="aRecupere(ins.id)?'background:#f0fdfa':'background:#F1F5F9'">
                                    <span class="font-bold text-sm" :style="'color:'+(aRecupere(ins.id)?'#0f766e':'#94A3B8')" x-text="(ins.etu_prenom||ins.etu_nom||'?').charAt(0).toUpperCase()"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold truncate" x-text="ins.etu_prenom+' '+ins.etu_nom"></p>
                                    <div class="flex items-center gap-2.5">
                                        <span style="font-size:11px;color:#94A3B8"><i class="ri-user-3-line"></i> <span x-text="ins.etu_matricule"></span></span>
                                        <span style="font-size:11px;color:#94A3B8"><i class="ri-graduation-cap-line"></i> <span x-text="classeLabel(ins.id_classe)"></span></span>
                                    </div>
                                </div>
                                <button @click="toggleRecuperation(ins)" class="px-3.5 py-1.5 rounded-full text-xs font-semibold flex items-center gap-1.5" :style="aRecupere(ins.id)?'background:#0d9488;color:#fff':'background:#F1F5F9;color:#64748B'">
                                    <i :class="aRecupere(ins.id)?'ri-checkbox-circle-fill':'ri-checkbox-blank-circle-line'"></i>
                                    <span x-text="aRecupere(ins.id)?'Récupéré':'Non récupéré'"></span>
                                </button>
                            </div>
                        </template>
                        <p x-show="!distInscriptions().length" class="text-center py-10 text-sm" style="color:#94A3B8">Aucun étudiant inscrit pour ce filtre</p>
                    </div>
                </div>
            </template>
        </div>
    </template>

    {{-- ═══ MODAL Type d'Article ═══ --}}
    <template x-if="typeModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="typeEditing?'Modifier le Type':'Nouveau Type d\'Article'"></h2>
                    <button @click="typeModal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div><label class="f-label">Libellé <span style="color:#EF4444">*</span></label><input type="text" x-model="typeForm.libelle_article_types" class="f-input"></div>
                    <div><label class="f-label">Slug</label><input type="text" x-model="typeForm.slug_article_types" class="f-input" placeholder="ex: uniformes"></div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="typeModal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="button" @click="submitType()" :disabled="typeSubmitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:#5A67D8" x-text="typeSubmitting?'...':'Enregistrer'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ═══ MODAL Article ═══ --}}
    <template x-if="articleModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="articleEditing?'Modifier l\'Article':'Nouvel Article'"></h2>
                    <button @click="articleModal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div><label class="f-label">Libellé <span style="color:#EF4444">*</span></label><input type="text" x-model="articleForm.libelle" class="f-input"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="f-label">Slug</label><input type="text" x-model="articleForm.slug" class="f-input" placeholder="ex: polo-bleu"></div>
                        <div><label class="f-label">Type d'Article <span style="color:#EF4444">*</span></label>
                            <select x-model.number="articleForm.article_type_id" class="f-select"><template x-for="t in articleTypes" :key="t.id"><option :value="t.id" x-text="t.libelle_article_types"></option></template></select>
                        </div>
                    </div>
                    <div><label class="f-label">Prix Unitaire (FCFA)</label><input type="number" min="0" x-model="articleForm.prix_unitaire" class="f-input" placeholder="0"></div>
                    <label class="flex items-start gap-2 p-3 rounded-lg" style="background:#F8FAFC">
                        <input type="checkbox" x-model="articleForm.inclus_scolarite" class="mt-0.5">
                        <span>
                            <span class="block text-sm font-medium" style="color:#1E293B">Inclus dans la scolarité</span>
                            <span class="block text-xs" style="color:#94A3B8">Article offert dans le cadre des frais de scolarité</span>
                        </span>
                    </label>
                    <div><label class="f-label">Description</label><textarea x-model="articleForm.description" rows="3" class="f-input"></textarea></div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="articleModal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="button" @click="submitArticle()" :disabled="articleSubmitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:#5A67D8" x-text="articleSubmitting?'...':'Enregistrer'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ═══ MODAL Variante ═══ --}}
    <template x-if="variantModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="variantEditing?'Modifier la Variante':'Nouvelle Variante'"></h2>
                    <button @click="variantModal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="f-label">Taille</label><input type="text" x-model="variantForm.taille" class="f-input" placeholder="ex: M, L, XL"></div>
                        <div><label class="f-label">Couleur</label><input type="text" x-model="variantForm.couleur" class="f-input" placeholder="ex: Bleu Marine"></div>
                    </div>
                    <div><label class="f-label">Référence</label><input type="text" x-model="variantForm.reference" class="f-input" placeholder="ex: ART-001-M-BLU"></div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="variantModal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="button" @click="submitVariant()" :disabled="variantSubmitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:#5A67D8" x-text="variantSubmitting?'...':'Enregistrer'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ═══ MODAL Ajuster Stock ═══ --}}
    <template x-if="adjustModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="'Ajuster le Stock — '+(adjustRow?.articleLabel||'')"></h2>
                    <button @click="adjustModal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="p-3 rounded-lg flex items-center gap-2" style="background:#F8FAFC"><i class="ri-archive-2-line" style="color:#5A67D8"></i><span class="text-sm" x-text="adjustRow?.articleLabel"></span></div>
                    <div x-show="adjustRow && (adjustRow.taille||adjustRow.couleur)" class="p-3 rounded-lg flex items-center gap-2" style="background:#F8FAFC"><i class="ri-equalizer-line" style="color:#0d9488"></i><span class="text-sm" x-text="[adjustRow?.taille,adjustRow?.couleur].filter(Boolean).join(' / ')"></span></div>
                    <div><label class="f-label">Nouvelle Quantité</label><input type="number" x-model.number="adjustQty" class="f-input" placeholder="0"></div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="adjustModal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="button" @click="submitAdjust()" :disabled="adjustSubmitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:#5A67D8" x-text="adjustSubmitting?'...':'Enregistrer'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ═══ MODAL Réapprovisionner ═══ --}}
    <template x-if="reapproModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="'Réapprovisionner — '+(reapproRow?.articleLabel||'')"></h2>
                    <button @click="reapproModal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="p-3 rounded-lg flex items-center gap-2" style="background:#F8FAFC"><i class="ri-archive-2-line" style="color:#5A67D8"></i><span class="text-sm" x-text="(reapproRow?.quantite||0)+' unités'"></span></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="f-label">Quantité à ajouter</label><input type="number" min="1" x-model.number="reapproAjout" class="f-input" placeholder="0"></div>
                        <div><label class="f-label">Prix d'achat unitaire (FCFA)</label><input type="number" min="0" x-model.number="reapproPrix" class="f-input" placeholder="0"></div>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="reapproModal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="button" @click="submitReappro()" :disabled="reapproSubmitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:#5A67D8" x-text="reapproSubmitting?'...':'Enregistrer'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ═══ MODAL Vente ═══ --}}
    <template x-if="venteModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" style="max-height:90vh;overflow-y:auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="venteEditing?'Modifier la Vente':'Nouvelle Vente à Étudiant'"></h2>
                    <button @click="venteModal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <label class="flex items-start gap-2 p-3 rounded-lg" style="background:#f0fdfa;border:1px solid #99f6e4">
                        <input type="checkbox" x-model="venteForm.inclus_scolarite" @change="onVenteInclusChange()" class="mt-0.5">
                        <span>
                            <span class="block text-sm font-medium" style="color:#0f766e">Inclus dans la scolarité</span>
                            <span class="block text-xs" style="color:#0d9488" x-text="venteForm.inclus_scolarite?'Cet article est offert dans le cadre de la scolarité — montant = 0':'Activer si l\'article est compris dans les frais de scolarité'"></span>
                        </span>
                    </label>
                    <div>
                        <label class="f-label">Étudiant (Inscription) <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(inscriptions.map(i=>({v:i.id,l:(i.etu_prenom+' '+i.etu_nom).trim()||('Inscription #'+i.id)})), venteForm.inscription_student_id, 'Sélectionner...')" class="relative" @click.outside="open=false">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop"><template x-for="o in filtered" :key="o.v"><div @click="select(o); venteForm.inscription_student_id=o.v" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div></template></div>
                        </div>
                    </div>
                    <div>
                        <label class="f-label">Article / Variante <span style="color:#EF4444">*</span></label>
                        <div x-data="sSelect(variants.map(v=>({v:v.id,l:variantLabel(v.id)})), venteForm.article_variant_id, 'Sélectionner...')" class="relative" @click.outside="open=false">
                            <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off">
                            <div x-show="open" class="ss-drop"><template x-for="o in filtered" :key="o.v"><div @click="select(o); venteForm.article_variant_id=o.v; onVenteVariantChange()" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div></template></div>
                        </div>
                    </div>
                    <div><label class="f-label">Quantité <span style="color:#EF4444">*</span></label><input type="number" min="1" x-model.number="venteForm.quantite" @input="onVenteVariantChange()" class="f-input"></div>
                    <template x-if="!venteForm.inclus_scolarite">
                        <div class="space-y-4">
                            <div><label class="f-label">Prix Unitaire (FCFA)</label><input type="number" min="0" x-model.number="venteForm.prix_unitaire" class="f-input"></div>
                            <div><label class="f-label">Montant Total (FCFA)</label><input type="number" min="0" x-model.number="venteForm.montant" class="f-input"></div>
                            <div><label class="f-label">Statut Paiement</label>
                                <select x-model.number="venteForm.statut_paiement" class="f-select"><option value="">—</option><template x-for="s in statuts" :key="s.id"><option :value="s.id" x-text="s.libelle"></option></template></select>
                            </div>
                        </div>
                    </template>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="venteModal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="button" @click="submitVente()" :disabled="venteSubmitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:#5A67D8" x-text="venteSubmitting?'...':'Enregistrer'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function stocksPage(typesData, articlesData, variantsData, stocksData, ventesData, inscriptionsData, classesData, statutsData){
    return {
        articleTypes: typesData, articles: articlesData, variants: variantsData, stocks: stocksData,
        ventes: ventesData, inscriptions: inscriptionsData, classes: classesData, statuts: statutsData,
        tab: 0,

        // Tab 0
        filterType: '', searchArticles: '', expandedId: null,
        // Tab 1
        searchStock: '', filterArticleStock: '',
        // Tab 2
        modeDistribution: false, searchVentes: '', filterStatutVentes: '',
        distVariantId: '', distFilterClasse: '', distSearch: '',

        // Modals
        typeModal:false, typeEditing:false, typeSubmitting:false, typeForm:{id:'',libelle_article_types:'',slug_article_types:''},
        articleModal:false, articleEditing:false, articleSubmitting:false, articleForm:{id:'',libelle:'',slug:'',description:'',prix_unitaire:0,inclus_scolarite:false,article_type_id:''},
        variantModal:false, variantEditing:false, variantSubmitting:false, variantForm:{id:'',article_id:'',taille:'',couleur:'',reference:''},
        adjustModal:false, adjustSubmitting:false, adjustRow:null, adjustQty:0,
        reapproModal:false, reapproSubmitting:false, reapproRow:null, reapproAjout:0, reapproPrix:0,
        venteModal:false, venteEditing:false, venteSubmitting:false, venteForm:{id:'',inclus_scolarite:false,inscription_student_id:'',article_variant_id:'',quantite:1,prix_unitaire:0,montant:0,statut_paiement:''},

        csrf(){ return document.querySelector('meta[name="csrf-token"]').content; },
        fmt(n){ return Math.round(Number(n)||0).toLocaleString('fr-FR').replace(/,/g,' '); },
        toast(message,type='info'){
            const colors={success:{bg:'rgba(22,163,74,.95)'},error:{bg:'rgba(239,68,68,.95)'},warning:{bg:'rgba(245,158,11,.95)'},info:{bg:'rgba(90,103,216,.95)'}};
            const style=colors[type]||colors.info; const t=document.createElement('div');
            t.style.cssText=`position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:12px 24px;border-radius:12px;font-size:13px;font-weight:500;background:${style.bg};color:#fff;z-index:9999;box-shadow:0 8px 32px rgba(0,0,0,.12);max-width:90%`;
            t.textContent=message; document.body.appendChild(t);
            setTimeout(()=>{ t.style.opacity='0'; t.style.transition='all .3s ease'; setTimeout(()=>t.remove(),300); },3500);
        },

        // ── Helpers communs ──
        typeName(id){ const t=this.articleTypes.find(t=>t.id===id); return t?t.libelle_article_types:'—'; },
        articleOf(id){ return this.articles.find(a=>a.id===id); },
        variantsFor(articleId){ return this.variants.filter(v=>v.article_id===articleId); },
        stockQty(variantId){ const s=this.stocks.find(s=>s.article_variant_id===variantId); return s?Number(s.quantite)||0:0; },
        totalStockForArticle(articleId){ return this.variantsFor(articleId).reduce((s,v)=>s+this.stockQty(v.id),0); },
        outOfStockCount(){ return this.variants.filter(v=>this.stockQty(v.id)<=0).length; },
        stockChipHtml(qty){
            let bg,fg;
            if(qty<=0){ bg='rgba(239,68,68,.08)'; fg='#dc2626'; }
            else if(qty<5){ bg='rgba(249,115,22,.08)'; fg='#c2410c'; }
            else { bg='rgba(22,163,74,.08)'; fg='#15803d'; }
            return `<span class="variant-chip" style="background:${bg};color:${fg}"><i class="ri-archive-line"></i>${qty} en stock</span>`;
        },
        variantLabel(variantId){
            const v=this.variants.find(v=>v.id===variantId);
            if(!v) return '—';
            const a=this.articleOf(v.article_id);
            const parts=[a?a.libelle:''];
            if(v.taille) parts.push(v.taille);
            if(v.couleur) parts.push(v.couleur);
            return parts.filter(Boolean).join(' — ');
        },
        studentLabel(inscriptionId){
            const ins=this.inscriptions.find(i=>i.id===inscriptionId);
            if(!ins) return 'Inscription #'+inscriptionId;
            return (ins.etu_prenom+' '+ins.etu_nom).trim() || ('Inscription #'+inscriptionId);
        },
        classeLabel(id){ const c=this.classes.find(c=>c.id===id); return c?c.libelle:'—'; },
        statutLabel(id){ const s=this.statuts.find(s=>s.id===id); return s?s.libelle:'—'; },
        statutColorStyle(id){
            const lbl=this.statutLabel(id).toLowerCase();
            if(lbl.includes('pay')) return 'background:rgba(22,163,74,.1);color:#16a34a';
            if(lbl.includes('pend')||lbl.includes('attent')) return 'background:rgba(249,115,22,.1);color:#f97316';
            if(lbl.includes('ann')) return 'background:rgba(239,68,68,.1);color:#ef4444';
            return 'background:#F1F5F9;color:#94A3B8';
        },
        isInclus(v){ return Number(v.montant||0)===0 && (!v.prix_unitaire || Number(v.prix_unitaire)===0); },

        // ── Tab 0 : Articles & Types ──
        filteredArticles(){
            return this.articles.filter(a=>{
                if(this.filterType && a.article_type_id!==this.filterType) return false;
                if(this.searchArticles.trim()){
                    const q=this.searchArticles.toLowerCase();
                    const hay=[a.libelle,a.description,this.typeName(a.article_type_id)].join(' ').toLowerCase();
                    if(!hay.includes(q)) return false;
                }
                return true;
            });
        },
        openTypeCreate(){ this.typeEditing=false; this.typeSubmitting=false; this.typeForm={id:'',libelle_article_types:'',slug_article_types:''}; this.typeModal=true; },
        openTypeEdit(t){ this.typeEditing=true; this.typeSubmitting=false; this.typeForm={id:t.id,libelle_article_types:t.libelle_article_types,slug_article_types:t.slug_article_types||''}; this.typeModal=true; },
        submitType(){
            if(!this.typeForm.libelle_article_types) return this.toast('Libellé requis','error');
            this.typeSubmitting=true;
            const url=this.typeEditing?'/stocks/types/'+this.typeForm.id:'/stocks/types';
            fetch(url,{method:this.typeEditing?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'},body:JSON.stringify(this.typeForm)})
                .then(async res=>{ if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); } const r=await res.json();
                    if(this.typeEditing){ const i=this.articleTypes.findIndex(t=>t.id===r.data.id); if(i!==-1) this.articleTypes[i]=r.data; } else this.articleTypes.push(r.data);
                    this.typeModal=false; this.typeSubmitting=false; this.toast(this.typeEditing?'Type modifié':'Type créé','success');
                }).catch(err=>{ this.typeSubmitting=false; this.toast(err.message,'error'); });
        },
        deleteType(t){
            const nb=this.articles.filter(a=>a.article_type_id===t.id).length;
            if(nb>0) return alert(nb+" article(s) utilisent ce type.\nSupprimez d'abord ces articles avant de supprimer le type.");
            if(!confirm('Supprimer "'+t.libelle_article_types+'" ?')) return;
            fetch('/stocks/types/'+t.id,{method:'DELETE',headers:{'X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); this.articleTypes=this.articleTypes.filter(x=>x.id!==t.id); if(this.filterType===t.id) this.filterType=''; this.toast('Type supprimé','success'); })
                .catch(err=>this.toast(err.message,'error'));
        },
        openArticleCreate(){ this.articleEditing=false; this.articleSubmitting=false; this.articleForm={id:'',libelle:'',slug:'',description:'',prix_unitaire:0,inclus_scolarite:false,article_type_id:this.articleTypes[0]?.id||''}; this.articleModal=true; },
        openArticleEdit(a){ this.articleEditing=true; this.articleSubmitting=false; this.articleForm={id:a.id,libelle:a.libelle,slug:a.slug||'',description:a.description||'',prix_unitaire:a.prix_unitaire||0,inclus_scolarite:!!a.inclus_scolarite,article_type_id:a.article_type_id}; this.articleModal=true; },
        submitArticle(){
            if(!this.articleForm.libelle||!this.articleForm.article_type_id) return this.toast('Champs requis manquants','error');
            this.articleSubmitting=true;
            const url=this.articleEditing?'/stocks/articles/'+this.articleForm.id:'/stocks/articles';
            fetch(url,{method:this.articleEditing?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'},body:JSON.stringify(this.articleForm)})
                .then(async res=>{ if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); } const r=await res.json();
                    if(this.articleEditing){ const i=this.articles.findIndex(a=>a.id===r.data.id); if(i!==-1) this.articles[i]=r.data; } else this.articles.unshift(r.data);
                    this.articleModal=false; this.articleSubmitting=false; this.toast(this.articleEditing?'Article modifié':'Article créé','success');
                }).catch(err=>{ this.articleSubmitting=false; this.toast(err.message,'error'); });
        },
        deleteArticle(a){
            const vs=this.variantsFor(a.id);
            const nbStocks=vs.reduce((s,v)=>s+(this.stocks.some(st=>st.article_variant_id===v.id)?1:0),0);
            const nbVentes=vs.reduce((s,v)=>s+this.ventes.filter(ve=>ve.article_variant_id===v.id).length,0);
            const details=[vs.length?vs.length+' variante(s)':null, nbStocks>0?nbStocks+' ligne(s) de stock':null, nbVentes>0?nbVentes+' vente(s) associée(s)':null].filter(Boolean).join(', ');
            if(!confirm((details?a.libelle+' — supprimera aussi : '+details:a.libelle)+'\n\nConfirmer la suppression ?')) return;
            fetch('/stocks/articles/'+a.id,{method:'DELETE',headers:{'X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur');
                    const vIds=vs.map(v=>v.id);
                    this.ventes=this.ventes.filter(ve=>!vIds.includes(ve.article_variant_id));
                    this.stocks=this.stocks.filter(st=>!vIds.includes(st.article_variant_id));
                    this.variants=this.variants.filter(v=>v.article_id!==a.id);
                    this.articles=this.articles.filter(x=>x.id!==a.id);
                    this.toast('Article supprimé','success');
                }).catch(err=>this.toast(err.message,'error'));
        },
        openVariantCreate(articleId){ this.variantEditing=false; this.variantSubmitting=false; this.variantForm={id:'',article_id:articleId,taille:'',couleur:'',reference:''}; this.variantModal=true; },
        openVariantEdit(articleId, v){ this.variantEditing=true; this.variantSubmitting=false; this.variantForm={id:v.id,article_id:articleId,taille:v.taille||'',couleur:v.couleur||'',reference:v.reference||''}; this.variantModal=true; },
        submitVariant(){
            this.variantSubmitting=true;
            const url=this.variantEditing?'/stocks/variants/'+this.variantForm.id:'/stocks/variants';
            fetch(url,{method:this.variantEditing?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'},body:JSON.stringify(this.variantForm)})
                .then(async res=>{ if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); } const r=await res.json();
                    if(this.variantEditing){ const i=this.variants.findIndex(v=>v.id===r.data.id); if(i!==-1) this.variants[i]=r.data; } else this.variants.push(r.data);
                    this.variantModal=false; this.variantSubmitting=false; this.toast(this.variantEditing?'Variante modifiée':'Variante créée','success');
                }).catch(err=>{ this.variantSubmitting=false; this.toast(err.message,'error'); });
        },
        deleteVariant(v){
            const nbStocks=this.stocks.filter(s=>s.article_variant_id===v.id).length;
            const nbVentes=this.ventes.filter(ve=>ve.article_variant_id===v.id).length;
            const parts=[v.taille,v.couleur,v.reference?('#'+v.reference):null].filter(Boolean).join(' / ');
            const details=[nbStocks>0?nbStocks+' ligne(s) de stock':null, nbVentes>0?nbVentes+' vente(s) associée(s)':null].filter(Boolean).join(', ');
            if(!confirm((details?(parts||'cette variante')+' — supprimera aussi : '+details:(parts||'cette variante'))+'\n\nConfirmer la suppression ?')) return;
            fetch('/stocks/variants/'+v.id,{method:'DELETE',headers:{'X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur');
                    this.ventes=this.ventes.filter(ve=>ve.article_variant_id!==v.id);
                    this.stocks=this.stocks.filter(s=>s.article_variant_id!==v.id);
                    this.variants=this.variants.filter(x=>x.id!==v.id);
                    this.toast('Variante supprimée','success');
                }).catch(err=>this.toast(err.message,'error'));
        },

        // ── Tab 1 : État du Stock ──
        stockRows(){
            const rows=[];
            for(const v of this.variants){
                const a=this.articleOf(v.article_id);
                if(!a) continue;
                if(this.filterArticleStock && a.id!==this.filterArticleStock) continue;
                const qty=this.stockQty(v.id);
                if(this.searchStock.trim()){
                    const q=this.searchStock.toLowerCase();
                    const hay=[a.libelle,v.taille,v.couleur,v.reference].join(' ').toLowerCase();
                    if(!hay.includes(q)) continue;
                }
                rows.push({ variantId:v.id, articleLabel:a.libelle, taille:v.taille||'', couleur:v.couleur||'', reference:v.reference||'', quantite:qty });
            }
            return rows.sort((a,b)=>a.articleLabel.localeCompare(b.articleLabel));
        },
        openAdjust(row){ this.adjustRow=row; this.adjustQty=row.quantite; this.adjustSubmitting=false; this.adjustModal=true; },
        submitAdjust(){
            this.adjustSubmitting=true;
            fetch('/stocks/adjust',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'},body:JSON.stringify({article_variant_id:this.adjustRow.variantId,quantite:this.adjustQty})})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); const r=await res.json();
                    const i=this.stocks.findIndex(s=>s.article_variant_id===this.adjustRow.variantId);
                    if(i!==-1) this.stocks[i]=r.data; else this.stocks.push(r.data);
                    this.adjustModal=false; this.adjustSubmitting=false; this.toast(r.message,'success');
                }).catch(err=>{ this.adjustSubmitting=false; this.toast(err.message,'error'); });
        },
        openReappro(row){ this.reapproRow=row; this.reapproAjout=0; this.reapproPrix=0; this.reapproSubmitting=false; this.reapproModal=true; },
        submitReappro(){
            if(!this.reapproAjout || this.reapproAjout<=0) return this.toast('Quantité invalide','error');
            this.reapproSubmitting=true;
            fetch('/stocks/reappro',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'},body:JSON.stringify({article_variant_id:this.reapproRow.variantId,ajout:this.reapproAjout,prix_achat:this.reapproPrix})})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); const r=await res.json();
                    const i=this.stocks.findIndex(s=>s.article_variant_id===this.reapproRow.variantId);
                    if(i!==-1) this.stocks[i]=r.data; else this.stocks.push(r.data);
                    this.reapproModal=false; this.reapproSubmitting=false; this.toast(r.message,'success');
                }).catch(err=>{ this.reapproSubmitting=false; this.toast(err.message,'error'); });
        },
        deleteStock(row){
            if(!confirm((row.articleLabel+(row.taille?' / '+row.taille:'')+(row.couleur?' / '+row.couleur:'')+' ('+row.quantite+' unité(s))')+'\n\nSupprimer cette ligne de stock ?')) return;
            const stock=this.stocks.find(s=>s.article_variant_id===row.variantId);
            if(!stock) return this.toast('Aucun stock enregistré pour cette variante.','warning');
            fetch('/stocks/stock/'+stock.id,{method:'DELETE',headers:{'X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); this.stocks=this.stocks.filter(s=>s.id!==stock.id); this.toast('Ligne de stock supprimée','success'); })
                .catch(err=>this.toast(err.message,'error'));
        },

        // ── Tab 2 : Ventes ──
        filteredVentes(){
            return this.ventes.filter(v=>{
                if(this.filterStatutVentes && v.statut_paiement!==this.filterStatutVentes) return false;
                if(this.searchVentes.trim()){
                    const q=this.searchVentes.toLowerCase();
                    const hay=[this.variantLabel(v.article_variant_id), this.studentLabel(v.inscription_student_id)].join(' ').toLowerCase();
                    if(!hay.includes(q)) return false;
                }
                return true;
            });
        },
        onVenteInclusChange(){
            if(this.venteForm.inclus_scolarite){ this.venteForm.montant=0; this.venteForm.prix_unitaire=0; }
            else this.onVenteVariantChange();
        },
        onVenteVariantChange(){
            if(this.venteForm.inclus_scolarite) return;
            const v=this.variants.find(v=>v.id===Number(this.venteForm.article_variant_id));
            if(!v) return;
            const a=this.articleOf(v.article_id);
            if(!a) return;
            const prix=Number(a.prix_unitaire)||0;
            const qty=Number(this.venteForm.quantite)||1;
            this.venteForm.prix_unitaire=prix;
            this.venteForm.montant=prix*qty;
        },
        openVenteCreate(){
            this.venteEditing=false; this.venteSubmitting=false;
            this.venteForm={id:'',inclus_scolarite:false,inscription_student_id:this.inscriptions[0]?.id||'',article_variant_id:this.variants[0]?.id||'',quantite:1,prix_unitaire:0,montant:0,statut_paiement:this.statuts[0]?.id||''};
            this.venteModal=true;
        },
        openVenteEdit(v){
            this.venteEditing=true; this.venteSubmitting=false;
            this.venteForm={id:v.id, inclus_scolarite: this.isInclus(v), inscription_student_id:v.inscription_student_id, article_variant_id:v.article_variant_id, quantite:v.quantite, prix_unitaire:v.prix_unitaire||0, montant:v.montant||0, statut_paiement:v.statut_paiement||''};
            this.venteModal=true;
        },
        submitVente(){
            if(!this.venteForm.inscription_student_id||!this.venteForm.article_variant_id) return this.toast('Champs requis manquants','error');
            this.venteSubmitting=true;
            const payload={...this.venteForm};
            if(payload.inclus_scolarite){ payload.montant=0; payload.prix_unitaire=0; payload.statut_paiement=''; }
            const url=this.venteEditing?'/stocks/ventes/'+this.venteForm.id:'/stocks/ventes';
            fetch(url,{method:this.venteEditing?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'},body:JSON.stringify(payload)})
                .then(async res=>{ if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); } const r=await res.json();
                    if(this.venteEditing){ const i=this.ventes.findIndex(v=>v.id===r.data.id); if(i!==-1) this.ventes[i]=r.data; }
                    else {
                        this.ventes.unshift(r.data);
                        const stock=this.stocks.find(s=>s.article_variant_id===r.data.article_variant_id);
                        if(stock) stock.quantite=Math.max(0, stock.quantite-Number(r.data.quantite||0));
                    }
                    this.venteModal=false; this.venteSubmitting=false; this.toast(this.venteEditing?'Vente modifiée':'Vente enregistrée','success');
                }).catch(err=>{ this.venteSubmitting=false; this.toast(err.message,'error'); });
        },
        deleteVente(v){
            if(!confirm('Supprimer cette vente ?')) return;
            fetch('/stocks/ventes/'+v.id,{method:'DELETE',headers:{'X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); this.ventes=this.ventes.filter(x=>x.id!==v.id); this.toast('Vente supprimée','success'); })
                .catch(err=>this.toast(err.message,'error'));
        },

        // ── Tab 2 : Distribution ──
        inclusVariants(){ return this.variants.filter(v=>{ const a=this.articleOf(v.article_id); return a && a.inclus_scolarite; }); },
        distInscriptions(){
            return this.inscriptions.filter(ins=>{
                if(this.distFilterClasse && ins.id_classe!==this.distFilterClasse) return false;
                if(this.distSearch.trim()){
                    const q=this.distSearch.toLowerCase();
                    const hay=(ins.etu_nom+' '+ins.etu_prenom+' '+ins.etu_matricule).toLowerCase();
                    if(!hay.includes(q)) return false;
                }
                return true;
            });
        },
        aRecupere(insId){
            if(!this.distVariantId) return false;
            return this.ventes.some(v=>v.inscription_student_id===insId && v.article_variant_id===Number(this.distVariantId));
        },
        distRecuperes(){ return this.distInscriptions().filter(ins=>this.aRecupere(ins.id)).length; },
        toggleRecuperation(ins){
            if(!this.distVariantId) return;
            const already=this.aRecupere(ins.id);
            if(already && !confirm('Marquer cet article comme non récupéré ?')) return;
            fetch('/stocks/distribution/toggle',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'},body:JSON.stringify({inscription_student_id:ins.id,article_variant_id:this.distVariantId})})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); const r=await res.json();
                    if(r.recupere){ this.ventes.unshift(r.data); } else { this.ventes=this.ventes.filter(v=>!(v.inscription_student_id===ins.id && v.article_variant_id===Number(this.distVariantId))); }
                }).catch(err=>this.toast(err.message,'error'));
        },
        marquerTous(){
            if(!this.distVariantId) return;
            const nonRecuperes=this.distInscriptions().filter(ins=>!this.aRecupere(ins.id));
            if(!nonRecuperes.length) return;
            if(!confirm(nonRecuperes.length+" étudiant(s) seront marqués comme ayant récupéré cet article.\n\nConfirmer ?")) return;
            fetch('/stocks/distribution/marquer-tous',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.csrf(),'Accept':'application/json'},body:JSON.stringify({article_variant_id:this.distVariantId,inscription_ids:nonRecuperes.map(i=>i.id)})})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); this.toast('Marquage effectué','success'); setTimeout(()=>location.reload(),700); })
                .catch(err=>this.toast(err.message,'error'));
        },
    };
}
</script>
@endpush
</x-app-layout>
