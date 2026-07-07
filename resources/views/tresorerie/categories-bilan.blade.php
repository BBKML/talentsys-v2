<x-app-layout title="Trésorerie — Catégories & Bilan">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none}
.f-select{width:100%;padding:10px 12px;background:#fff;border:1px solid #E2E8F0;border-radius:8px;font-size:13px;color:#1E293B;outline:none}
.f-table-container{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #E2E8F0;overflow:hidden}
.f-table-header th{padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.06em;background:#F8FAFC;border-bottom:1px solid #E2E8F0}
.f-table-row td{padding:12px 16px;font-size:13px;color:#334155;border-bottom:1px solid #F1F5F9}
.f-table-row:hover{background:#F8FAFC}
.f-badge{display:inline-flex;padding:4px 10px;border-radius:8px;font-size:12px;font-weight:700}
.cb-tab{padding:10px 20px;border-radius:8px;font-size:13px;font-weight:700;border:1px solid #E2E8F0;background:transparent;color:#64748B;cursor:pointer}
.cb-tab.active{background:#5A67D8;border-color:#5A67D8;color:#fff}
.bilan-card{flex:1;background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);padding:20px;display:flex;align-items:center;gap:14px}
</style>
@endpush

@php
$categoriesJson = $categories->map(fn($c) => ['id'=>$c->id,'code'=>$c->code,'libelle'=>$c->libelle,'type_categorie'=>$c->type_categorie,'id_statut'=>$c->id_statut]);
$operationsJson = $operations->map(fn($o) => ['libelle'=>$o->libelle,'type_operation'=>$o->type_operation,'origine'=>$o->origine,'montant'=>$o->montant,'date'=>$o->date]);
$paiementsFlux = $paiements->map(fn($p) => [
    'libelle'=>'Scolarité — '.($p->inscription?->etudiant ? $p->inscription->etudiant->nom.' '.$p->inscription->etudiant->prenom : 'Étudiant'),
    'type_operation'=>'Entrée','origine'=>'scolarite','montant'=>$p->montant_verse,'date'=>$p->date ? substr((string)$p->date,0,10) : null,
]);
$salairesFlux = $salaires->map(fn($s) => [
    'libelle'=>'Salaire — '.trim(($s->enseignant?->prenom??'').' '.($s->enseignant?->nom??'')).' ('.($s->mois??'').')',
    'type_operation'=>'Sortie','origine'=>'salaire','montant'=>$s->salaire_net ?? $s->salaire_brut,'date'=>$s->date_paiement ? substr((string)$s->date_paiement,0,10) : $s->mois,
]);
@endphp

<div x-data="categoriesBilanPage({{ $categoriesJson }}, {{ $operationsJson }}, {{ $paiementsFlux }}, {{ $salairesFlux }})" class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Catégories &amp; Bilan</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8">Catégories comptables et bilan financier</p>
        </div>
        <button x-show="tab==='categories'" @click="openCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90" style="background:#5A67D8">
            <i class="ri-add-line"></i> Nouvelle Catégorie
        </button>
    </div>

    <div class="flex gap-2">
        <button class="cb-tab" :class="tab==='categories'?'active':''" @click="tab='categories'">Catégories</button>
        <button class="cb-tab" :class="tab==='bilan'?'active':''" @click="tab='bilan'">Bilan</button>
    </div>

    <template x-if="tab==='categories'">
        <div class="f-table-container">
            <div class="flex items-center px-5 py-3 border-b border-slate-100">
                <div class="relative">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-300"></i>
                    <input x-model="search" type="text" placeholder="Rechercher..." class="pl-9 pr-4 py-2 rounded-lg text-sm border border-slate-200 bg-slate-50 outline-none" style="min-width:240px">
                </div>
            </div>
            <table class="w-full">
                <thead class="f-table-header"><tr><th>Code</th><th>Libellé</th><th>Type</th><th>Statut</th><th style="text-align:right">Actions</th></tr></thead>
                <tbody>
                    <template x-for="c in filteredCats()" :key="c.id">
                        <tr class="f-table-row">
                            <td class="font-bold" x-text="c.code"></td>
                            <td class="font-bold" x-text="c.libelle"></td>
                            <td><span class="f-badge" :style="c.type_categorie==='Recette'?'background:rgba(22,163,74,.1);color:#16a34a':'background:rgba(239,68,68,.1);color:#ef4444'" x-text="c.type_categorie"></span></td>
                            <td><span class="f-badge" :style="c.id_statut===1?'background:rgba(22,163,74,.1);color:#16a34a':'background:rgba(239,68,68,.1);color:#ef4444'" x-text="c.id_statut===1?'Actif':'Inactif'"></span></td>
                            <td style="text-align:right">
                                <button @click="openEdit(c)" class="w-8 h-8 rounded-lg hover:bg-indigo-50 inline-flex items-center justify-center" style="color:#5A67D8"><i class="ri-edit-2-line text-[15px]"></i></button>
                                <button @click="deleteCat(c)" class="w-8 h-8 rounded-lg hover:bg-red-50 inline-flex items-center justify-center" style="color:#ef4444"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p x-show="!filteredCats().length" class="text-center py-10 text-sm" style="color:#94A3B8">Aucune catégorie</p>
        </div>
    </template>

    <template x-if="tab==='bilan'">
        <div class="space-y-5">
            <div class="flex gap-4 flex-wrap">
                <div class="bilan-card"><div><p class="text-xs" style="color:#94A3B8">Total Recettes</p><p class="text-lg font-bold" style="color:#16a34a" x-text="(totalEntrees()/1000000).toFixed(1)+'M FCFA'"></p></div></div>
                <div class="bilan-card"><div><p class="text-xs" style="color:#94A3B8">Total Dépenses</p><p class="text-lg font-bold" style="color:#ef4444" x-text="(totalSorties()/1000000).toFixed(1)+'M FCFA'"></p></div></div>
                <div class="bilan-card"><div><p class="text-xs" style="color:#94A3B8">Solde Net</p><p class="text-lg font-bold" :style="'color:'+(solde()>=0?'#5A67D8':'#ef4444')" x-text="(Math.abs(solde())/1000000).toFixed(1)+'M FCFA'"></p></div></div>
            </div>
            <div class="bg-white rounded-2xl p-6" style="box-shadow:0 1px 3px rgba(0,0,0,.06)">
                <div class="flex items-center justify-between mb-4">
                    <p class="font-bold" style="color:#1E293B">Détail des Opérations</p>
                    <span class="text-xs" style="color:#94A3B8" x-text="allFlux().length+' opération(s)'"></span>
                </div>
                <template x-for="op in allFlux()" :key="op.libelle+op.date">
                    <div class="flex items-center gap-3 mb-2 p-3 rounded-lg" style="background:#F8FAFC" :style="'border-left:3px solid '+(op.type_operation==='Entrée'?'#16a34a':'#ef4444')">
                        <div class="flex-1">
                            <p style="font-size:12px;font-weight:500" x-text="op.libelle"></p>
                            <p x-show="op.date" style="font-size:10px;color:#94A3B8" x-text="op.date"></p>
                        </div>
                        <span x-show="op.origine" class="f-badge" style="font-size:9px" :style="'background:'+origineColor(op.origine)+'1a;color:'+origineColor(op.origine)" x-text="op.origine"></span>
                        <span class="font-bold" style="font-size:13px" :style="'color:'+(op.type_operation==='Entrée'?'#16a34a':'#ef4444')" x-text="(op.type_operation==='Entrée'?'+ ':'- ')+fmt(op.montant)+' FCFA'"></span>
                    </div>
                </template>
                <p x-show="!allFlux().length" class="text-center py-8 text-sm" style="color:#94A3B8">Aucune opération enregistrée</p>
            </div>
        </div>
    </template>

    <template x-if="modal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="editing?'Modifier Catégorie':'Nouvelle Catégorie'"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="f-label">Code <span style="color:#EF4444">*</span></label><input type="text" x-model="form.code" class="f-input" placeholder="Ex: CAT-01"></div>
                        <div><label class="f-label">Libellé <span style="color:#EF4444">*</span></label><input type="text" x-model="form.libelle" class="f-input" placeholder="Ex: Scolarité"></div>
                    </div>
                    <div><label class="f-label">Type <span style="color:#EF4444">*</span></label>
                        <select x-model="form.type_categorie" class="f-select"><option value="Recette">Recette</option><option value="Dépense">Dépense</option></select>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="modal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="button" @click="submitForm()" :disabled="submitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:#5A67D8" x-text="submitting?'...':'Enregistrer'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function categoriesBilanPage(categoriesData, operationsData, paiementsData, salairesData){
    return {
        categories: categoriesData, operations: operationsData, paiementsFlux: paiementsData, salairesFlux: salairesData,
        tab: 'categories', search:'',
        modal:false, editing:false, submitting:false,
        form:{id:'',code:'',libelle:'',type_categorie:'Recette'},

        filteredCats(){
            if(!this.search.trim()) return this.categories;
            const q=this.search.toLowerCase();
            return this.categories.filter(c=>(c.code||'').toLowerCase().includes(q)||(c.libelle||'').toLowerCase().includes(q)||(c.type_categorie||'').toLowerCase().includes(q));
        },
        allFlux(){
            return [...this.operations, ...this.paiementsFlux, ...this.salairesFlux].slice().sort((a,b)=>(b.date||'').localeCompare(a.date||''));
        },
        totalEntrees(){ return this.allFlux().filter(o=>o.type_operation==='Entrée').reduce((s,o)=>s+Number(o.montant||0),0); },
        totalSorties(){ return this.allFlux().filter(o=>o.type_operation==='Sortie').reduce((s,o)=>s+Number(o.montant||0),0); },
        solde(){ return this.totalEntrees()-this.totalSorties(); },
        fmt(n){ return Math.abs(Number(n)||0).toLocaleString('fr-FR').replace(/,/g,' '); },
        origineColor(o){ return o==='scolarite'?'#5A67D8':o==='salaire'?'#f97316':o==='stock'?'#0d9488':'#94A3B8'; },

        openCreate(){ this.editing=false; this.submitting=false; this.form={id:'',code:'',libelle:'',type_categorie:'Recette'}; this.modal=true; },
        openEdit(c){ this.editing=true; this.submitting=false; this.form={id:c.id,code:c.code,libelle:c.libelle,type_categorie:c.type_categorie}; this.modal=true; },
        submitForm(){
            if(!this.form.code||!this.form.libelle) return this.toast('Champs requis manquants','error');
            this.submitting=true;
            const url=this.editing?'/tresorerie/categories/'+this.form.id:'/tresorerie/categories';
            const method=this.editing?'PUT':'POST';
            fetch(url,{method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify(this.form)})
                .then(async res=>{
                    if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); }
                    const r=await res.json();
                    const flat={id:r.data.id,code:r.data.code,libelle:r.data.libelle,type_categorie:r.data.type_categorie,id_statut:r.data.id_statut};
                    if(this.editing){ const idx=this.categories.findIndex(c=>c.id===flat.id); if(idx!==-1) this.categories[idx]=flat; }
                    else this.categories.unshift(flat);
                    this.modal=false; this.submitting=false;
                    this.toast(this.editing?'Catégorie modifiée':'Catégorie créée','success');
                }).catch(err=>{ this.submitting=false; this.toast(err.message,'error'); });
        },
        deleteCat(c){
            if(!confirm('Supprimer "'+c.libelle+'" ?')) return;
            fetch('/tresorerie/categories/'+c.id,{method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); this.categories=this.categories.filter(x=>x.id!==c.id); this.toast('Catégorie supprimée','success'); })
                .catch(err=>this.toast(err.message,'error'));
        },
        toast(message,type='info'){
            const colors={success:{bg:'rgba(22,163,74,.95)'},error:{bg:'rgba(239,68,68,.95)'},warning:{bg:'rgba(245,158,11,.95)'},info:{bg:'rgba(90,103,216,.95)'}};
            const style=colors[type]||colors.info; const t=document.createElement('div');
            t.style.cssText=`position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:12px 24px;border-radius:12px;font-size:13px;font-weight:500;background:${style.bg};color:#fff;z-index:9999;box-shadow:0 8px 32px rgba(0,0,0,.12);max-width:90%`;
            t.textContent=message; document.body.appendChild(t);
            setTimeout(()=>{ t.style.opacity='0'; t.style.transition='all .3s ease'; setTimeout(()=>t.remove(),300); },3500);
        },
    };
}
</script>
@endpush
</x-app-layout>
