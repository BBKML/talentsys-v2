<x-app-layout title="Découpage Année — Académique">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none;transition:all .15s}
.f-input:focus{background:#fff;box-shadow:0 0 0 2px var(--primary)44}
.tbl-th{font-size:11px;font-weight:600;color:#94A3B8;text-transform:uppercase;letter-spacing:.07em;padding:11px 16px;text-align:left}
.tbl-td{padding:13px 16px;font-size:13px;color:#475569}
.act-btn{width:34px;height:34px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;cursor:pointer;border:none;background:transparent}
.page-btn{width:32px;height:32px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s}
</style>
@endpush

@php
$data = $decoupages->map(fn($d) => [
    'id'                => $d->id,
    'libelle'           => $d->libelle,
    'type'              => $d->type ?? '',
    'ordre'             => $d->ordre ?? 1,
    'date_debut'        => $d->date_debut ?? '',
    'date_fin'          => $d->date_fin ?? '',
    'id_annee_scolaire' => $d->id_annee_scolaire,
    'annee'             => $d->annee?->libelle ?? '—',
]);
$anneesJson    = $annees->map(fn($a) => ['id' => $a->id, 'libelle' => $a->libelle, 'active' => (bool)$a->active]);
$anneeActiveId = $anneeActive?->id ?? '';
$typesList     = ['Semestre', 'Trimestre', 'Session'];
@endphp

<div x-data="page({{ $data }}, {{ $anneesJson }}, {{ $anneeActiveId ?: 'null' }})" class="space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold" style="color:#1E293B">Découpage de l'Année</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="items.length"></span> découpage(s)</p>
        </div>
        <button @click="openCreate()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-90" style="background:var(--primary)">
            <i class="ri-add-line"></i> Nouveau Découpage
        </button>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:rgba(34,197,94,.08);color:#15803d;border:1px solid rgba(34,197,94,.18)">
        <i class="ri-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    @if(!$anneeActive)
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:rgba(245,158,11,.08);color:#B45309;border:1px solid rgba(245,158,11,.2)">
        <i class="ri-alert-fill"></i> Aucune année scolaire active. <a href="{{ route('annees.index') }}" class="underline font-semibold">Activer une année</a> pour voir son découpage.
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="relative flex-1" style="max-width:280px">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1"></i>
                <input x-model="search" @input="currentPage=1" type="text" placeholder="Rechercher..." class="f-input" style="padding:8px 12px 8px 34px">
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
                <th class="tbl-th" style="width:15%">Type</th>
                <th class="tbl-th text-center" style="width:70px">Ordre</th>
                <th class="tbl-th" style="width:15%">Début</th>
                <th class="tbl-th" style="width:15%">Fin</th>
                <th class="tbl-th" style="width:15%">Année</th>
                <th class="tbl-th text-right" style="width:90px">Actions</th>
            </tr></thead>
            <tbody>
                <template x-if="paginated.length===0">
                    <tr><td colspan="7" class="py-20 text-center">
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#F1F5F9"><i class="ri-layout-grid-line text-3xl" style="color:#CBD5E1"></i></div>
                        <p class="text-sm font-semibold" style="color:#64748B">Aucun découpage enregistré</p>
                    </td></tr>
                </template>
                <template x-for="r in paginated" :key="r.id">
                    <tr style="border-bottom:1px solid #F8FAFC" class="hover:bg-slate-50 transition-colors">
                        <td class="tbl-td font-semibold" style="color:#1E293B" x-text="r.libelle"></td>
                        <td class="tbl-td">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md" style="background:rgba(90,103,216,.1);color:var(--primary)" x-text="r.type||'—'"></span>
                        </td>
                        <td class="tbl-td text-center"><span class="text-xs font-bold font-mono px-2 py-0.5 rounded-md" style="background:#F1F5F9;color:#64748B" x-text="r.ordre"></span></td>
                        <td class="tbl-td text-[12px]" style="color:#64748B" x-text="r.date_debut||'—'"></td>
                        <td class="tbl-td text-[12px]" style="color:#64748B" x-text="r.date_fin||'—'"></td>
                        <td class="tbl-td text-[12px]" style="color:#94A3B8" x-text="r.annee"></td>
                        <td class="tbl-td">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit(r)" class="act-btn hover:bg-indigo-50" style="color:#94A3B8"><i class="ri-edit-2-line text-[15px]"></i></button>
                                <form :action="'/decoupage-annee/'+r.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer ce découpage ?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn hover:bg-red-50" style="color:#CBD5E1"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
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
            <div class="bg-white rounded-2xl shadow-2xl w-full" style="max-width:520px" @click.stop>
                <div class="flex items-center gap-3 px-6 py-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(90,103,216,.12)"><i class="ri-layout-grid-fill text-xl" style="color:var(--primary)"></i></div>
                    <div class="flex-1"><h2 class="text-[15px] font-bold" style="color:#1E293B" x-text="editing?'Modifier le découpage':'Nouveau Découpage'"></h2></div>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line text-lg" style="color:#94A3B8"></i></button>
                </div>
                <div style="height:1px;background:#F1F5F9;margin:0 24px"></div>
                <form :action="editing?'/decoupage-annee/'+form.id:'{{ route('decoupage.store') }}'" method="POST" @submit="submitting=true">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="px-6 pt-5 pb-2 space-y-4">
                        <div>
                            <label class="f-label">Libellé <span style="color:#EF4444">*</span></label>
                            <input type="text" name="libelle" :value="form.libelle" required class="f-input" placeholder="Ex : Semestre 1">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="f-label">Année Scolaire <span style="color:#EF4444">*</span></label>
                                <div x-data="sSelect(annees.map(a=>({v:a.id,l:a.libelle})), form.id_annee_scolaire, 'Rechercher une année...')"
                                     class="relative" @click.outside="open=false">
                                    <input type="hidden" name="id_annee_scolaire" :value="v">
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
                                <label class="f-label">Type <span style="color:#EF4444">*</span></label>
                                <div x-data="sSelect([{v:'Semestre',l:'Semestre'},{v:'Trimestre',l:'Trimestre'},{v:'Session',l:'Session'}], form.type, 'Type de découpage...')"
                                     class="relative" @click.outside="open=false">
                                    <input type="hidden" name="type" :value="v">
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
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="f-label">Ordre <span style="color:#EF4444">*</span></label>
                                <input type="number" name="ordre" :value="form.ordre" class="f-input" placeholder="1" min="1" required>
                            </div>
                            <div>
                                <label class="f-label">Date début <span style="color:#EF4444">*</span></label>
                                <div class="relative">
                                    <i class="ri-calendar-line absolute left-3 top-1/2 -translate-y-1/2 text-sm pointer-events-none" style="color:#CBD5E1"></i>
                                    <input type="date" name="date_debut" :value="form.date_debut" class="f-input" style="padding-left:32px" required>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="f-label">Date fin <span style="color:#EF4444">*</span></label>
                            <div class="relative">
                                <i class="ri-calendar-line absolute left-3 top-1/2 -translate-y-1/2 text-sm pointer-events-none" style="color:#CBD5E1"></i>
                                <input type="date" name="date_fin" :value="form.date_fin" class="f-input" style="padding-left:32px" required>
                            </div>
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
function page(data, annees, anneeActiveId){
    return {
        items:data, annees:annees, anneeActiveId:anneeActiveId,
        search:'', perPage:10, currentPage:1, modal:false, editing:false, submitting:false,
        form:{id:'',libelle:'',type:'Semestre',ordre:1,date_debut:'',date_fin:'',id_annee_scolaire:''},
        get filtered(){ const q=this.search.toLowerCase(); return q?this.items.filter(i=>i.libelle.toLowerCase().includes(q)||i.annee.toLowerCase().includes(q)):this.items; },
        get paginated(){ const s=(this.currentPage-1)*this.perPage; return this.filtered.slice(s,s+this.perPage); },
        get totalPages(){ return Math.max(1,Math.ceil(this.filtered.length/this.perPage)); },
        get pages(){ const p=[],t=this.totalPages,c=this.currentPage; for(let i=Math.max(1,c-2);i<=Math.min(t,c+2);i++)p.push(i); return p; },
        get info(){ if(!this.filtered.length)return '0 résultat(s)'; const s=(this.currentPage-1)*this.perPage+1,e=Math.min(this.currentPage*this.perPage,this.filtered.length); return `${s}–${e} sur ${this.filtered.length} résultat(s)`; },
        openCreate(){
            this.editing=false; this.submitting=false;
            this.form={id:'',libelle:'',type:'Semestre',ordre:1,date_debut:'',date_fin:'',id_annee_scolaire:this.anneeActiveId||''};
            this.modal=true;
        },
        openEdit(r){ this.editing=true; this.submitting=false; this.form={...r}; this.modal=true; },
    }
}
</script>
@endpush
</x-app-layout>
