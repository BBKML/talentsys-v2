<x-app-layout title="Salles — Établissement">

@push('styles')
<style>
.f-label { font-size:12px; font-weight:600; color:#475569; margin-bottom:6px; display:block; }
.f-input  { width:100%; padding:10px 12px; background:#F1F5F9; border:none; border-radius:8px; font-size:13px; color:#1E293B; outline:none; transition:all .15s; }
.f-input:focus { background:#fff; box-shadow:0 0 0 2px var(--primary)44; }
.tbl-th  { font-size:11px; font-weight:600; color:#94A3B8; text-transform:uppercase; letter-spacing:.07em; padding:11px 16px; text-align:left; }
.tbl-td  { padding:13px 16px; font-size:13px; color:#475569; }
.act-btn { width:34px; height:34px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; transition:all .15s; cursor:pointer; border:none; background:transparent; }
.page-btn{ width:32px; height:32px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; transition:all .15s; }
.badge-on  { background:rgba(34,197,94,.12);  color:#15803d; }
.badge-off { background:rgba(239,68,68,.12);  color:#dc2626; }
</style>
@endpush

@php
$sallesJson = $salles->map(fn($s) => [
    'id'       => $s->id,
    'libelle'  => $s->libelle ?? '',
    'code'     => $s->code ?? '',
    'type'     => $s->type ?? '',
    'id_statut'=> $s->id_statut,
    'actif'    => $s->id_statut == 1,
]);
$types = [
    'Salle de cours','Amphithéâtre','Laboratoire',
    'Salle informatique','Bibliothèque','Salle de sport','Bureau','Salle de réunion',
];
@endphp

<div x-data="sallesPage({{ $sallesJson }})" class="space-y-5">

    {{-- ── En-tête ──────────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold" style="color:#1E293B">Salles</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8">
                <span x-text="items.length"></span> salle(s) enregistrée(s)
            </p>
        </div>
        <button @click="openCreate()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-90 transition"
                style="background:var(--primary)">
            <i class="ri-add-line text-base"></i>
            Nouvelle salle
        </button>
    </div>

    {{-- ── Flash ────────────────────────────────────────────────────────────── --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
         style="background:rgba(34,197,94,.08); color:#15803d; border:1px solid rgba(34,197,94,.18)">
        <i class="ri-check-circle-fill text-base"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
         style="background:rgba(239,68,68,.08); color:#dc2626; border:1px solid rgba(239,68,68,.18)">
        <i class="ri-error-warning-fill text-base"></i> {{ session('error') }}
    </div>
    @endif

    {{-- ── Cartes résumé ───────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:rgba(90,103,216,.12)">
                <i class="ri-door-open-fill text-xl" style="color:var(--primary)"></i>
            </div>
            <div>
                <p class="text-xs font-semibold" style="color:#94A3B8">Total</p>
                <p class="text-2xl font-bold" style="color:#1E293B" x-text="items.length"></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:rgba(34,197,94,.12)">
                <i class="ri-checkbox-circle-fill text-xl" style="color:#16a34a"></i>
            </div>
            <div>
                <p class="text-xs font-semibold" style="color:#94A3B8">Actives</p>
                <p class="text-2xl font-bold" style="color:#1E293B"
                   x-text="items.filter(s=>s.actif).length"></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:rgba(239,68,68,.1)">
                <i class="ri-close-circle-fill text-xl" style="color:#dc2626"></i>
            </div>
            <div>
                <p class="text-xs font-semibold" style="color:#94A3B8">Inactives</p>
                <p class="text-2xl font-bold" style="color:#1E293B"
                   x-text="items.filter(s=>!s.actif).length"></p>
            </div>
        </div>
    </div>

    {{-- ── DataTable ───────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Barre recherche / perPage --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="relative flex-1" style="max-width:280px">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1"></i>
                <input x-model="search" @input="currentPage=1" type="text"
                       placeholder="Rechercher une salle..."
                       class="f-input" style="padding:8px 12px 8px 34px">
            </div>
            <div class="ml-auto flex items-center gap-2 text-xs font-semibold" style="color:#94A3B8">
                <span>Lignes/page :</span>
                <select x-model.number="perPage" @change="currentPage=1"
                        class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs font-semibold bg-white outline-none"
                        style="color:#475569">
                    <option>10</option><option>25</option><option>50</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr style="border-bottom:1px solid #F1F5F9; background:#FAFBFC">
                        <th class="tbl-th" style="width:110px">Code</th>
                        <th class="tbl-th" style="width:30%">Libellé</th>
                        <th class="tbl-th" style="width:25%">Type</th>
                        <th class="tbl-th text-center" style="width:120px">Statut</th>
                        <th class="tbl-th text-right" style="width:100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="paginated.length === 0">
                        <tr>
                            <td colspan="5" class="py-20 text-center">
                                <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center"
                                     style="background:#F1F5F9">
                                    <i class="ri-door-open-line text-3xl" style="color:#CBD5E1"></i>
                                </div>
                                <p class="text-sm font-semibold" style="color:#64748B">Aucune salle trouvée</p>
                                <p class="text-xs mt-1" style="color:#94A3B8">Cliquez sur "Nouvelle salle" pour commencer</p>
                            </td>
                        </tr>
                    </template>

                    <template x-for="s in paginated" :key="s.id">
                        <tr style="border-bottom:1px solid #F8FAFC" class="hover:bg-slate-50 transition-colors">

                            {{-- Code badge --}}
                            <td class="tbl-td">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-bold font-mono tracking-wide"
                                      style="background:rgba(90,103,216,.1); color:var(--primary)"
                                      x-text="s.code || '—'"></span>
                            </td>

                            {{-- Libellé --}}
                            <td class="tbl-td">
                                <span class="font-semibold" style="font-size:13px; color:#1E293B"
                                      x-text="s.libelle || '—'"></span>
                            </td>

                            {{-- Type --}}
                            <td class="tbl-td">
                                <span class="text-[12px]" style="color:#64748B" x-text="s.type || '—'"></span>
                            </td>

                            {{-- Statut (toggle) --}}
                            <td class="tbl-td text-center">
                                <form :action="'/salles/'+s.id+'/statut'" method="POST" style="display:inline">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[11px] font-bold border-0 cursor-pointer hover:opacity-75 transition"
                                            :class="s.actif ? 'badge-on' : 'badge-off'">
                                        <span class="w-1.5 h-1.5 rounded-full inline-block"
                                              :style="'background:' + (s.actif ? '#16a34a' : '#dc2626')"></span>
                                        <span x-text="s.actif ? 'Actif' : 'Inactif'"></span>
                                    </button>
                                </form>
                            </td>

                            {{-- Actions --}}
                            <td class="tbl-td">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEdit(s)" title="Modifier"
                                            class="act-btn hover:bg-indigo-50 hover:text-indigo-600"
                                            style="color:#94A3B8">
                                        <i class="ri-edit-2-line text-[15px]"></i>
                                    </button>
                                    <form :action="'/salles/'+s.id" method="POST" style="display:inline"
                                          @submit.prevent="if(confirm('Supprimer la salle « '+s.libelle+' » ?')) $el.submit()">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Supprimer"
                                                class="act-btn hover:bg-red-50"
                                                style="color:#CBD5E1">
                                            <i class="ri-delete-bin-2-line text-[15px]"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100">
            <span class="text-xs" style="color:#94A3B8" x-text="paginationInfo"></span>
            <div class="flex items-center gap-1">
                <button @click="currentPage--" :disabled="currentPage===1"
                        class="page-btn hover:bg-gray-100 disabled:opacity-30" style="color:#64748B">
                    <i class="ri-arrow-left-s-line"></i>
                </button>
                <template x-for="p in pageNumbers" :key="p">
                    <button @click="currentPage=p" class="page-btn"
                            :style="currentPage===p ? 'background:var(--primary); color:#fff' : 'background:transparent; color:#475569'"
                            :class="currentPage!==p ? 'hover:bg-gray-100' : ''"
                            x-text="p"></button>
                </template>
                <button @click="currentPage++" :disabled="currentPage===totalPages"
                        class="page-btn hover:bg-gray-100 disabled:opacity-30" style="color:#64748B">
                    <i class="ri-arrow-right-s-line"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ══ MODAL ══════════════════════════════════════════════════════════════ --}}
    <div x-show="modal" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
         style="background:rgba(15,23,42,.5)">
        <div class="bg-white rounded-2xl shadow-2xl w-full" style="max-width:460px" @click.stop>

            {{-- En-tête modal --}}
            <div class="flex items-center gap-3 px-6 py-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:rgba(90,103,216,.12)">
                    <i class="ri-door-open-fill text-xl" style="color:var(--primary)"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-[15px] font-bold" style="color:#1E293B"
                        x-text="editing ? 'Modifier la salle' : 'Nouvelle salle'"></h2>
                    <p class="text-xs mt-0.5" style="color:#94A3B8"
                       x-text="editing ? 'Modifiez les informations de la salle' : 'Renseignez les informations de la salle'"></p>
                </div>
                <button @click="modal=false"
                        class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100">
                    <i class="ri-close-line text-lg" style="color:#94A3B8"></i>
                </button>
            </div>
            <div style="height:1px; background:#F1F5F9; margin:0 24px"></div>

            <form :action="editing ? '/salles/'+form.id : '{{ route('salles.store') }}'"
                  method="POST" @submit="submitting=true">
                @csrf
                <template x-if="editing">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="px-6 pt-5 pb-2 space-y-4">

                    {{-- Libellé + Code sur 2 colonnes --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="f-label">Libellé <span style="color:#EF4444">*</span></label>
                            <input type="text" name="libelle" :value="form.libelle" required
                                   class="f-input" placeholder="Ex : Amphi A">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="f-label">Code</label>
                            <input type="text" name="code" :value="form.code"
                                   class="f-input" placeholder="Ex : A101">
                        </div>
                    </div>

                    {{-- Type --}}
                    <div>
                        <label class="f-label">Type de salle</label>
                        <select name="type" class="f-input" style="cursor:pointer">
                            <option value="">— Sélectionner un type —</option>
                            @foreach($types as $t)
                            <option :selected="form.type === '{{ $t }}'" value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4">
                    <button type="button" @click="modal=false"
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50 transition"
                            style="color:#64748B">Annuler</button>
                    <button type="submit" :disabled="submitting"
                            class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition disabled:opacity-60"
                            style="background:var(--primary)">
                        <span x-text="submitting ? 'Enregistrement...' : 'Enregistrer'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function sallesPage(data) {
    return {
        items: data,
        search: '', perPage: 10, currentPage: 1,
        modal: false, editing: false, submitting: false,
        form: { id:'', libelle:'', code:'', type:'' },

        typeIcon(type) {
            const map = {
                'Salle de cours'    : 'ri-presentation-2-line',
                'Amphithéâtre'      : 'ri-mic-2-line',
                'Laboratoire'       : 'ri-flask-line',
                'Salle informatique': 'ri-computer-line',
                'Bibliothèque'      : 'ri-book-2-line',
                'Salle de sport'    : 'ri-football-line',
                'Bureau'            : 'ri-briefcase-4-line',
                'Salle de réunion'  : 'ri-group-line',
            };
            return map[type] || 'ri-door-open-line';
        },

        typeColor(type) {
            const map = {
                'Salle de cours'    : '#6366F1',
                'Amphithéâtre'      : '#8B5CF6',
                'Laboratoire'       : '#0EA5E9',
                'Salle informatique': '#06B6D4',
                'Bibliothèque'      : '#F59E0B',
                'Salle de sport'    : '#10B981',
                'Bureau'            : '#64748B',
                'Salle de réunion'  : '#EC4899',
            };
            return map[type] || '#94A3B8';
        },

        get filtered() {
            const q = this.search.toLowerCase();
            if (!q) return this.items;
            return this.items.filter(s =>
                s.libelle.toLowerCase().includes(q) ||
                (s.code||'').toLowerCase().includes(q) ||
                (s.type||'').toLowerCase().includes(q)
            );
        },
        get paginated() {
            const s = (this.currentPage-1)*this.perPage;
            return this.filtered.slice(s, s+this.perPage);
        },
        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length/this.perPage)); },
        get pageNumbers() {
            const p=[],t=this.totalPages,c=this.currentPage;
            for(let i=Math.max(1,c-2);i<=Math.min(t,c+2);i++) p.push(i);
            return p;
        },
        get paginationInfo() {
            if(!this.filtered.length) return '0 résultat(s)';
            const s=(this.currentPage-1)*this.perPage+1;
            const e=Math.min(this.currentPage*this.perPage,this.filtered.length);
            return `${s}–${e} sur ${this.filtered.length} résultat(s)`;
        },

        openCreate() {
            this.editing=false; this.submitting=false;
            this.form={id:'',libelle:'',code:'',type:''};
            this.modal=true;
        },
        openEdit(s) {
            this.editing=true; this.submitting=false;
            this.form={...s};
            this.modal=true;
        },
    }
}
</script>
@endpush

</x-app-layout>
