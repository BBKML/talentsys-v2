<x-app-layout title="Trésorerie — Opérations">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none}
.f-select{width:100%;padding:10px 12px;background:#fff;border:1px solid #E2E8F0;border-radius:8px;font-size:13px;color:#1E293B;outline:none}
.f-table-container{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #E2E8F0;overflow:hidden}
.f-table-header th{padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.06em;background:#F8FAFC;border-bottom:1px solid #E2E8F0}
.f-table-row td{padding:12px 16px;font-size:13px;color:#334155;border-bottom:1px solid #F1F5F9;vertical-align:middle}
.f-table-row:hover{background:#F8FAFC}
.f-badge{display:inline-flex;padding:4px 10px;border-radius:8px;font-size:12px;font-weight:700}
.origin-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:10px;font-size:11px;font-weight:700;border:1px solid}
.kpi-card{flex:1;background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.05);padding:16px;display:flex;align-items:center;gap:12px}
.kpi-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.chip{padding:7px 12px;border-radius:20px;font-size:12px;font-weight:600;border:1px solid #E2E8F0;background:#fff;color:#475569;cursor:pointer}
.chip.active{background:#5A67D8;border-color:#5A67D8;color:#fff}
.ss-drop{position:absolute;top:100%;left:0;right:0;margin-top:4px;max-height:220px;overflow-y:auto;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.12);border:1px solid #E2E8F0;z-index:30}
.ss-item{padding:9px 14px;font-size:13px;color:#334155;cursor:pointer}
.ss-item:hover{background:#F1F5F9}
.ss-sel{background:#EEF2FF;color:#5A67D8;font-weight:600}
</style>
@endpush

@php
$operationsJson = $operations->map(fn($o) => [
    'id'=>$o->id,'libelle'=>$o->libelle,'montant'=>$o->montant,'type_operation'=>$o->type_operation,
    'date'=>$o->date,'origine'=>$o->origine,'id_categorie_comptable'=>$o->id_categorie_comptable,
    'categorie_libelle'=>$o->categorie?->libelle,'readonly'=>false,
]);
$paiementsFlux = $paiements->map(fn($p) => [
    'libelle'=>'Paiement scolarité — '.($p->inscription?->etudiant ? $p->inscription->etudiant->nom.' '.$p->inscription->etudiant->prenom : 'Étudiant'),
    'type_operation'=>'Entrée','origine'=>'scolarite','montant'=>$p->montant_verse,
    'date'=>$p->date ? substr((string)$p->date,0,10) : null,'readonly'=>true,
]);
$salairesFlux = $salaires->map(fn($s) => [
    'libelle'=>'Salaire — '.trim(($s->enseignant?->prenom??'').' '.($s->enseignant?->nom??'')).' ('.($s->mois??'').')',
    'type_operation'=>'Sortie','origine'=>'salaire','montant'=>$s->salaire_net ?? $s->salaire_brut,
    'date'=>$s->date_paiement ? substr((string)$s->date_paiement,0,10) : $s->mois,'readonly'=>true,
]);
$categoriesJson = $categories->map(fn($c) => ['id'=>$c->id,'libelle'=>$c->libelle]);
@endphp

<div x-data="operationsPage({{ $operationsJson }}, {{ $paiementsFlux }}, {{ $salairesFlux }}, {{ $categoriesJson }})" class="space-y-5">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold" style="color:#1E293B">Trésorerie — Flux Financiers</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="filtered.length"></span> mouvement(s)</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="initPlanComptable()" :disabled="initializing" class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold border disabled:opacity-50" style="color:#4f46e5;border-color:#4f46e5">
                <i class="ri-settings-3-line"></i> <span x-text="initializing?'Init...':'Init. Plan Comptable'"></span>
            </button>
            <button @click="genererEcritures()" :disabled="generating" class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold border disabled:opacity-50" style="color:#0d9488;border-color:#0d9488">
                <i class="ri-magic-line"></i> <span x-text="generating?'Génération...':'Générer écritures'"></span>
            </button>
            <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90" style="background:#5A67D8">
                <i class="ri-add-line"></i> Nouvelle Opération
            </button>
        </div>
    </div>

    <div class="flex gap-3 flex-wrap">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(22,163,74,.1)"><i class="ri-arrow-up-line" style="color:#16a34a"></i></div>
            <div><p class="text-[11px]" style="color:#94A3B8">Total Entrées</p><p class="text-sm font-bold" style="color:#16a34a" x-text="fmt(totalE())+' FCFA'"></p></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(239,68,68,.1)"><i class="ri-arrow-down-line" style="color:#ef4444"></i></div>
            <div><p class="text-[11px]" style="color:#94A3B8">Total Sorties</p><p class="text-sm font-bold" style="color:#ef4444" x-text="fmt(totalS())+' FCFA'"></p></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" :style="'background:'+(solde()>=0?'rgba(90,103,216,.1)':'rgba(239,68,68,.1)')"><i class="ri-wallet-3-line" :style="'color:'+(solde()>=0?'#5A67D8':'#ef4444')"></i></div>
            <div><p class="text-[11px]" style="color:#94A3B8">Solde Net</p><p class="text-sm font-bold" :style="'color:'+(solde()>=0?'#5A67D8':'#ef4444')" x-text="fmt(solde())+' FCFA'"></p></div>
        </div>
        <div class="kpi-card" style="background:#f0fdfa;border:1px solid #99f6e4">
            <div class="kpi-icon" style="background:rgba(13,148,136,.15)"><i class="ri-archive-line" style="color:#0d9488"></i></div>
            <div>
                <p class="text-[10px] font-bold" style="color:#0f766e">STOCK</p>
                <p style="font-size:11px;color:#0f766e">Recettes : <span x-text="fmt(stockE())"></span></p>
                <p style="font-size:11px;color:#0f766e">Dépenses : <span x-text="fmt(stockS())"></span></p>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 items-center">
        <button class="chip" :class="!filterOrigine?'active':''" @click="filterOrigine=''">Toutes origines</button>
        <button class="chip" :class="filterOrigine==='manuel'?'active':''" @click="filterOrigine='manuel'">Manuelles</button>
        <button class="chip" :class="filterOrigine==='stock'?'active':''" @click="filterOrigine='stock'">Stock</button>
        <button class="chip" :class="filterOrigine==='scolarite'?'active':''" @click="filterOrigine='scolarite'">Scolarité</button>
        <button class="chip" :class="filterOrigine==='salaire'?'active':''" @click="filterOrigine='salaire'">Salaires</button>
        <span class="w-2"></span>
        <button class="chip" :class="!filterType?'active':''" @click="filterType=''">Tous types</button>
        <button class="chip" :class="filterType==='Entrée'?'active':''" @click="filterType='Entrée'">Entrées</button>
        <button class="chip" :class="filterType==='Sortie'?'active':''" @click="filterType='Sortie'">Sorties</button>
    </div>

    <div class="f-table-container">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
            <div class="relative">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-300"></i>
                <input x-model="search" type="text" placeholder="Rechercher..." class="pl-9 pr-4 py-2 rounded-lg text-sm border border-slate-200 bg-slate-50 outline-none" style="min-width:240px">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="f-table-header"><tr><th>Libellé</th><th>Origine</th><th>Type</th><th>Catégorie</th><th>Montant</th><th>Date</th><th style="text-align:right">Actions</th></tr></thead>
                <tbody>
                    <template x-for="op in filtered" :key="op.id||op.libelle+op.date">
                        <tr class="f-table-row">
                            <td class="font-bold" x-text="op.libelle"></td>
                            <td>
                                <template x-if="op.origine==='stock'"><span class="origin-badge" style="color:#0d9488;border-color:#0d9488"><i class="ri-archive-line"></i> Stock</span></template>
                                <template x-if="op.origine==='scolarite'"><span class="origin-badge" style="color:#5A67D8;border-color:#5A67D8"><i class="ri-school-line"></i> Scolarité</span></template>
                                <template x-if="op.origine==='salaire'"><span class="origin-badge" style="color:#f97316;border-color:#f97316"><i class="ri-group-line"></i> Salaire</span></template>
                                <template x-if="!op.origine"><span style="font-size:11px;color:#94A3B8">Manuel</span></template>
                            </td>
                            <td><span class="f-badge" :style="op.type_operation==='Entrée'?'background:rgba(22,163,74,.1);color:#16a34a':'background:rgba(239,68,68,.1);color:#ef4444'" x-text="op.type_operation"></span></td>
                            <td style="font-size:12px" x-text="op.categorie_libelle||'—'"></td>
                            <td class="font-bold" :style="op.type_operation==='Entrée'?'color:#16a34a':'color:#ef4444'" x-text="(op.type_operation==='Entrée'?'+ ':'- ')+fmt(op.montant)+' FCFA'"></td>
                            <td style="font-size:12px" x-text="op.date||'—'"></td>
                            <td style="text-align:right">
                                <template x-if="!op.readonly">
                                    <div class="flex justify-end gap-1">
                                        <button @click="openEdit(op)" class="w-8 h-8 rounded-lg hover:bg-indigo-50 inline-flex items-center justify-center" style="color:#5A67D8"><i class="ri-edit-2-line text-[15px]"></i></button>
                                        <button @click="deleteOp(op)" class="w-8 h-8 rounded-lg hover:bg-red-50 inline-flex items-center justify-center" style="color:#ef4444"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                                    </div>
                                </template>
                                <span x-show="op.readonly" style="font-size:11px;color:#CBD5E1;font-style:italic">Automatique</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p x-show="!filtered.length" class="text-center py-10 text-sm" style="color:#94A3B8">Aucun mouvement</p>
        </div>
    </div>

    <template x-if="modal">
        <div class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(15,23,42,.45)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold" style="color:#1E293B" x-text="editing?'Modifier Opération':'Nouvelle Opération'"></h2>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line" style="color:#64748B"></i></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div><label class="f-label">Libellé <span style="color:#EF4444">*</span></label><input type="text" x-model="form.libelle" class="f-input"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="f-label">Type</label>
                            <select x-model="form.type_operation" class="f-select"><option value="Entrée">Entrée</option><option value="Sortie">Sortie</option></select>
                        </div>
                        <div>
                            <label class="f-label">Catégorie</label>
                            <select x-model="form.id_categorie_comptable" class="f-select">
                                <option value="">Aucune</option>
                                <template x-for="c in categories" :key="c.id"><option :value="c.id" x-text="c.libelle"></option></template>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="f-label">Montant (FCFA) <span style="color:#EF4444">*</span></label><input type="number" min="0" x-model="form.montant" class="f-input"></div>
                        <div><label class="f-label">Date <span style="color:#EF4444">*</span></label><input type="date" x-model="form.date" class="f-input"></div>
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
function operationsPage(operationsData, paiementsData, salairesData, categoriesData){
    return {
        operations: operationsData, paiementsFlux: paiementsData, salairesFlux: salairesData, categories: categoriesData,
        filterOrigine:'', filterType:'', search:'',
        modal:false, editing:false, submitting:false, initializing:false, generating:false,
        form:{id:'',libelle:'',montant:'',type_operation:'Entrée',id_categorie_comptable:'',date:''},

        get allFlux(){
            const flux = [...this.operations, ...this.paiementsFlux, ...this.salairesFlux];
            return flux.slice().sort((a,b)=>(b.date||'').localeCompare(a.date||''));
        },
        get filtered(){
            return this.allFlux.filter(op=>{
                if(this.filterOrigine==='stock' && op.origine!=='stock') return false;
                if(this.filterOrigine==='scolarite' && op.origine!=='scolarite') return false;
                if(this.filterOrigine==='salaire' && op.origine!=='salaire') return false;
                if(this.filterOrigine==='manuel' && op.origine) return false;
                if(this.filterType && op.type_operation!==this.filterType) return false;
                if(this.search.trim()){
                    const q=this.search.toLowerCase();
                    if(!(op.libelle||'').toLowerCase().includes(q) && !(op.type_operation||'').toLowerCase().includes(q)) return false;
                }
                return true;
            });
        },
        fmt(n){ return Math.abs(Number(n)||0).toLocaleString('fr-FR').replace(/,/g,' '); },
        totalE(){ return this.filtered.filter(o=>o.type_operation==='Entrée').reduce((s,o)=>s+Number(o.montant||0),0); },
        totalS(){ return this.filtered.filter(o=>o.type_operation==='Sortie').reduce((s,o)=>s+Number(o.montant||0),0); },
        solde(){ return this.totalE()-this.totalS(); },
        stockE(){ return this.operations.filter(o=>o.origine==='stock'&&o.type_operation==='Entrée').reduce((s,o)=>s+Number(o.montant||0),0); },
        stockS(){ return this.operations.filter(o=>o.origine==='stock'&&o.type_operation==='Sortie').reduce((s,o)=>s+Number(o.montant||0),0); },

        openCreate(){ this.editing=false; this.submitting=false; this.form={id:'',libelle:'',montant:'',type_operation:'Entrée',id_categorie_comptable:'',date:new Date().toISOString().slice(0,10)}; this.modal=true; },
        openEdit(op){ this.editing=true; this.submitting=false; this.form={id:op.id,libelle:op.libelle,montant:op.montant,type_operation:op.type_operation,id_categorie_comptable:op.id_categorie_comptable||'',date:op.date}; this.modal=true; },
        submitForm(){
            if(!this.form.libelle||this.form.montant===''||!this.form.date) return this.toast('Champs requis manquants','error');
            this.submitting=true;
            const url=this.editing?'/tresorerie/operations/'+this.form.id:'/tresorerie/operations';
            const method=this.editing?'PUT':'POST';
            fetch(url,{method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify(this.form)})
                .then(async res=>{
                    if(!res.ok){ const e=await res.json(); throw new Error(e.message||'Erreur'); }
                    const r=await res.json();
                    const cat=this.categories.find(c=>c.id===Number(r.data.id_categorie_comptable));
                    const flat={id:r.data.id,libelle:r.data.libelle,montant:r.data.montant,type_operation:r.data.type_operation,date:r.data.date,origine:null,id_categorie_comptable:r.data.id_categorie_comptable,categorie_libelle:cat?cat.libelle:null,readonly:false};
                    if(this.editing){ const idx=this.operations.findIndex(o=>o.id===flat.id); if(idx!==-1) this.operations[idx]=flat; }
                    else this.operations.unshift(flat);
                    this.modal=false; this.submitting=false;
                    this.toast(this.editing?'Opération modifiée':'Opération enregistrée','success');
                }).catch(err=>{ this.submitting=false; this.toast(err.message,'error'); });
        },
        deleteOp(op){
            if(!confirm('Supprimer "'+op.libelle+'" ?')) return;
            fetch('/tresorerie/operations/'+op.id,{method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); this.operations=this.operations.filter(o=>o.id!==op.id); this.toast('Opération supprimée','success'); })
                .catch(err=>this.toast(err.message,'error'));
        },
        initPlanComptable(){
            this.initializing=true;
            fetch('/tresorerie/init-plan-comptable',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); const r=await res.json(); this.toast(r.message,'success'); })
                .catch(err=>this.toast(err.message,'error')).finally(()=>this.initializing=false);
        },
        genererEcritures(){
            this.generating=true;
            fetch('/tresorerie/generer-ecritures',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}})
                .then(async res=>{ if(!res.ok) throw new Error('Erreur'); const r=await res.json(); this.toast(r.count>0?(r.count+' écriture(s) comptable(s) générée(s) avec succès !'):'Toutes les écritures sont déjà à jour.', r.count>0?'success':'info'); })
                .catch(err=>this.toast(err.message,'error')).finally(()=>this.generating=false);
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
