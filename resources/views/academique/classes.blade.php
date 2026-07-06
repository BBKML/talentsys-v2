<x-app-layout title="Classes — Académique">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none;transition:all .15s}
.f-input:focus{background:#fff;box-shadow:0 0 0 2px var(--primary)44}
.tbl-th{font-size:11px;font-weight:600;color:#94A3B8;text-transform:uppercase;letter-spacing:.07em;padding:11px 16px;text-align:left}
.tbl-td{padding:13px 16px;font-size:13px;color:#475569}
.act-btn{width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;cursor:pointer;border:none;background:transparent}
.page-btn{width:32px;height:32px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s}
</style>
@endpush

@php
$data = $classes->map(fn($c) => [
    'id'               => $c->id,
    'libelle'          => $c->libelle,
    'effectif'         => $c->effectif ?? 0,
    'capacite_max'     => $c->capacite_max ?? 0,
    'id_filiere'       => $c->id_filiere,
    'filiere'          => $c->filiere?->libelle ?? '—',
    'id_niveau'        => $c->id_niveau,
    'niveau'           => $c->niveau?->libelle ?? '—',
    'niveau_code'      => $c->niveau?->code ?? '',
    'id_annee_scolaire'=> $c->id_annee_scolaire,
    'annee'            => $c->annee?->libelle ?? '—',
    'actif'            => $c->id_statut == 1,
]);
$filieresJson = $filieres->map(fn($f) => ['id' => $f->id, 'libelle' => $f->libelle]);
$niveauxJson  = $niveaux->map(fn($n) => ['id' => $n->id, 'libelle' => $n->libelle, 'code' => $n->code ?? '']);
@endphp

<div x-data="page({{ $data }}, {{ $filieresJson }}, {{ $niveauxJson }})" class="space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold" style="color:#1E293B">Classes</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="filtered.length"></span> classe(s)</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Filtre par niveau --}}
            <div x-data="{open:false}" class="relative" @click.outside="open=false">
                <button @click="open=!open" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 bg-white hover:bg-gray-50" style="color:#475569">
                    <span x-text="filterNiveau?'Niveau : '+filterNiveau:'Tous les niveaux'"></span>
                    <i class="ri-filter-3-line text-sm" style="color:#94A3B8"></i>
                </button>
                <div x-show="open" class="absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-20 min-w-[180px] py-1">
                    <div @click="filterNiveau='';currentPage=1;open=false" class="px-4 py-2 text-sm cursor-pointer hover:bg-gray-50" :style="!filterNiveau?'color:var(--primary);font-weight:600':''">Tous les niveaux</div>
                    <template x-for="n in uniqueNiveaux" :key="n">
                        <div @click="filterNiveau=n;currentPage=1;open=false" class="px-4 py-2 text-sm cursor-pointer hover:bg-gray-50" :style="filterNiveau===n?'color:var(--primary);font-weight:600':''">
                            <span x-text="n"></span>
                        </div>
                    </template>
                </div>
            </div>
            <button @click="openCreate()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-90" style="background:var(--primary)">
                <i class="ri-add-line"></i> Nouvelle Classe
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:rgba(34,197,94,.08);color:#15803d;border:1px solid rgba(34,197,94,.18)">
        <i class="ri-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:rgba(90,103,216,.12)"><i class="ri-group-2-fill text-xl" style="color:var(--primary)"></i></div>
            <div><p class="text-xs font-semibold" style="color:#94A3B8">Total classes</p><p class="text-2xl font-bold" style="color:#1E293B" x-text="items.length"></p></div>
        </div>
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:rgba(34,197,94,.12)"><i class="ri-checkbox-circle-fill text-xl" style="color:#16a34a"></i></div>
            <div><p class="text-xs font-semibold" style="color:#94A3B8">Actives</p><p class="text-2xl font-bold" style="color:#1E293B" x-text="items.filter(i=>i.actif).length"></p></div>
        </div>
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:rgba(245,158,11,.1)"><i class="ri-user-fill text-xl" style="color:#D97706"></i></div>
            <div><p class="text-xs font-semibold" style="color:#94A3B8">Total effectifs</p><p class="text-2xl font-bold" style="color:#1E293B" x-text="items.reduce((s,i)=>s+(i.effectif||0),0)"></p></div>
        </div>
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:rgba(100,116,139,.1)"><i class="ri-door-fill text-xl" style="color:#64748B"></i></div>
            <div><p class="text-xs font-semibold" style="color:#94A3B8">Total capacité</p><p class="text-2xl font-bold" style="color:#1E293B" x-text="items.reduce((s,i)=>s+(i.capacite_max||0),0)"></p></div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="relative flex-1" style="max-width:280px">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1"></i>
                <input x-model="search" @input="currentPage=1" type="text" placeholder="Rechercher une classe..." class="f-input" style="padding:8px 12px 8px 34px">
            </div>
            <div class="ml-auto flex items-center gap-2 text-xs font-semibold" style="color:#94A3B8">
                Lignes/page :
                <select x-model.number="perPage" @change="currentPage=1" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs bg-white outline-none" style="color:#475569">
                    <option>10</option><option>25</option><option>50</option>
                </select>
            </div>
        </div>
        <table class="w-full">
            <thead><tr style="border-bottom:1px solid #F1F5F9;background:#FAFBFC">
                <th class="tbl-th" style="width:28%">Libellé</th>
                <th class="tbl-th" style="width:28%">Filière</th>
                <th class="tbl-th" style="width:18%">Niveau</th>
                <th class="tbl-th" style="width:18%">Effectif</th>
                <th class="tbl-th text-right" style="width:100px">Actions</th>
            </tr></thead>
            <tbody>
                <template x-if="paginated.length===0">
                    <tr><td colspan="5" class="py-20 text-center">
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#F1F5F9"><i class="ri-group-2-line text-3xl" style="color:#CBD5E1"></i></div>
                        <p class="text-sm font-semibold" style="color:#64748B">Aucune classe enregistrée</p>
                    </td></tr>
                </template>
                <template x-for="r in paginated" :key="r.id">
                    <tr style="border-bottom:1px solid #F8FAFC" class="hover:bg-slate-50 transition-colors">
                        {{-- Libellé --}}
                        <td class="tbl-td">
                            <span class="font-semibold text-sm" style="color:var(--primary)" x-text="r.libelle"></span>
                        </td>
                        {{-- Filière badge --}}
                        <td class="tbl-td">
                            <span class="inline-flex px-3 py-1 rounded-xl text-[12px] font-semibold" style="background:#F1F5F9;color:#475569" x-text="r.filiere"></span>
                        </td>
                        {{-- Niveau badge coloré --}}
                        <td class="tbl-td">
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full text-[11px] font-bold"
                                  :style="niveauStyle(r.niveau_code)"
                                  x-text="r.niveau_code||r.niveau"></span>
                        </td>
                        {{-- Effectif / Capacité --}}
                        <td class="tbl-td">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold" style="color:#1E293B">
                                    <span x-text="r.effectif"></span><span style="color:#94A3B8">/<span x-text="r.capacite_max||'∞'"></span></span>
                                </span>
                                <span class="text-[11px] font-bold" style="color:#16a34a"
                                      x-text="r.capacite_max?(Math.round(r.effectif/r.capacite_max*100)+'%'):'0%'"></span>
                            </div>
                        </td>
                        {{-- Actions --}}
                        <td class="tbl-td">
                            <div class="flex items-center justify-end gap-1">
                                <a :href="'/classes/'+r.id+'/etudiants'" class="act-btn hover:bg-teal-50" style="color:#0d9488" title="Étudiants"><i class="ri-group-fill text-[15px]"></i></a>
                                <button @click="openEdit(r)" class="act-btn hover:bg-indigo-50" style="color:#6366f1"><i class="ri-edit-2-line text-[15px]"></i></button>
                                <form :action="'/classes/'+r.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer cette classe ?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn hover:bg-red-50" style="color:#ef4444"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100">
            <span class="text-xs" style="color:#94A3B8" x-text="info"></span>
            <div class="flex items-center gap-1">
                <button @click="currentPage--" :disabled="currentPage===1" class="page-btn hover:bg-gray-100 disabled:opacity-30" style="color:#64748B"><i class="ri-arrow-left-s-line"></i></button>
                <template x-for="p in pages" :key="p"><button @click="currentPage=p" class="page-btn" :style="currentPage===p?'background:var(--primary);color:#fff':'color:#475569'" :class="currentPage!==p?'hover:bg-gray-100':''" x-text="p"></button></template>
                <button @click="currentPage++" :disabled="currentPage===totalPages" class="page-btn hover:bg-gray-100 disabled:opacity-30" style="color:#64748B"><i class="ri-arrow-right-s-line"></i></button>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <template x-if="modal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(15,23,42,.5)">
            <div class="bg-white rounded-2xl shadow-2xl w-full" style="max-width:540px" @click.stop>
                <div class="flex items-center gap-3 px-6 py-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(90,103,216,.12)"><i class="ri-group-2-fill text-xl" style="color:var(--primary)"></i></div>
                    <div class="flex-1"><h2 class="text-[15px] font-bold" style="color:#1E293B" x-text="editing?'Modifier la classe':'Nouvelle classe'"></h2></div>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line text-lg" style="color:#94A3B8"></i></button>
                </div>
                <div style="height:1px;background:#F1F5F9;margin:0 24px"></div>
                <form :action="editing?'/classes/'+form.id:'{{ route('classes.store') }}'" method="POST" @submit="submitting=true">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="px-6 pt-5 pb-2 space-y-4">
                        <div>
                            <label class="f-label">Libellé <span style="color:#EF4444">*</span></label>
                            <input type="text" name="libelle" :value="form.libelle" required class="f-input" placeholder="Ex : L1 Info A">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="f-label">Filière <span style="color:#EF4444">*</span></label>
                                <div x-data="sSelect(filieres.map(f=>({v:f.id,l:f.libelle})), form.id_filiere, 'Rechercher une filière...')"
                                     class="relative" @click.outside="open=false">
                                    <input type="hidden" name="id_filiere" :value="v">
                                    <div class="relative">
                                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1;pointer-events:none"></i>
                                        <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" style="padding-left:32px" :placeholder="ph" autocomplete="off">
                                    </div>
                                    <div x-show="open" class="ss-drop">
                                        <div x-show="!filtered.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                        <template x-for="o in filtered" :key="o.v">
                                            <div @click="select(o)" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="f-label">Niveau <span style="color:#EF4444">*</span></label>
                                <div x-data="sSelect(niveaux.map(n=>({v:n.id,l:n.libelle+' ('+n.code+')'})), form.id_niveau, 'Rechercher un niveau...')"
                                     class="relative" @click.outside="open=false">
                                    <input type="hidden" name="id_niveau" :value="v">
                                    <div class="relative">
                                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1;pointer-events:none"></i>
                                        <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" style="padding-left:32px" :placeholder="ph" autocomplete="off">
                                    </div>
                                    <div x-show="open" class="ss-drop">
                                        <div x-show="!filtered.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                        <template x-for="o in filtered" :key="o.v">
                                            <div @click="select(o)" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="f-label">Capacité max</label>
                            <input type="number" name="capacite_max" :value="form.capacite_max" class="f-input" placeholder="—" min="0">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 px-6 py-4">
                        <button type="button" @click="modal=false" class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50" style="color:#64748B">Annuler</button>
                        <button type="submit" :disabled="submitting" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60" style="background:var(--primary)" x-text="submitting?'...':'Enregistrer'"></button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function niveauStyle(code) {
    const c = (code||'').charAt(0).toUpperCase();
    if(c==='L') return 'background:rgba(99,102,241,.15);color:#4f46e5';
    if(c==='M') return 'background:rgba(16,185,129,.15);color:#059669';
    if(c==='D') return 'background:rgba(14,165,233,.15);color:#0284c7';
    if(c==='B') return 'background:rgba(245,158,11,.15);color:#d97706';
    return 'background:#F1F5F9;color:#64748B';
}

function page(data, filieres, niveaux){
    return {
        items:data, filieres:filieres, niveaux:niveaux,
        search:'', filterNiveau:'', perPage:10, currentPage:1, modal:false, editing:false, submitting:false,
        form:{id:'',libelle:'',id_filiere:'',id_niveau:'',capacite_max:''},
        get uniqueNiveaux(){
            return [...new Set(this.items.map(i=>i.niveau).filter(Boolean))].sort();
        },
        get filtered(){
            let list = this.items;
            if(this.filterNiveau) list = list.filter(i=>i.niveau===this.filterNiveau);
            const q = this.search.toLowerCase();
            if(q) list = list.filter(i=>i.libelle.toLowerCase().includes(q)||i.filiere.toLowerCase().includes(q)||i.niveau.toLowerCase().includes(q));
            return list;
        },
        get paginated(){ const s=(this.currentPage-1)*this.perPage; return this.filtered.slice(s,s+this.perPage); },
        get totalPages(){ return Math.max(1,Math.ceil(this.filtered.length/this.perPage)); },
        get pages(){ const p=[],t=this.totalPages,c=this.currentPage; for(let i=Math.max(1,c-2);i<=Math.min(t,c+2);i++)p.push(i); return p; },
        get info(){ if(!this.filtered.length)return '0 résultat(s)'; const s=(this.currentPage-1)*this.perPage+1,e=Math.min(this.currentPage*this.perPage,this.filtered.length); return `${s}–${e} sur ${this.filtered.length} résultat(s)`; },
        openCreate(){ this.editing=false; this.submitting=false; this.form={id:'',libelle:'',id_filiere:'',id_niveau:'',capacite_max:''}; this.modal=true; },
        openEdit(r){ this.editing=true; this.submitting=false; this.form={...r}; this.modal=true; },
    }
}
</script>
@endpush
</x-app-layout>
