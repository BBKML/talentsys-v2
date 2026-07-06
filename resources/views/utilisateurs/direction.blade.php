<x-app-layout title="Direction — TalentSys">

@push('styles')
<style>
.f-label { font-size:12px; font-weight:600; color:#475569; margin-bottom:6px; display:block; }
.f-input { width:100%; padding:10px 12px; background:#F1F5F9; border:none; border-radius:8px; font-size:13px; color:#1E293B; outline:none; transition:all .15s; }
.f-input:focus { background:#fff; box-shadow:0 0 0 2px var(--primary)44; }
.tbl-th { font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase; letter-spacing:.06em; padding:12px 16px; text-align:left; }
.tbl-td { padding:12px 16px; font-size:13px; color:#475569; }
.action-btn { width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; transition:all .15s; cursor:pointer; border:none; background:transparent; }
.page-btn { width:32px; height:32px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; transition:all .15s; }
.fs-trigger { display:flex; align-items:center; gap:8px; padding:10px 12px; background:#F1F5F9; border-radius:8px; cursor:pointer; user-select:none; }
.fs-item { display:flex; align-items:center; justify-content:space-between; padding:14px 24px; cursor:pointer; font-size:14px; color:#1E293B; transition:background .1s; }
.fs-item:hover { background:#F8FAFC; }
.fs-item.active { color:var(--primary); font-weight:600; background: color-mix(in srgb, var(--primary) 8%, white); }
</style>
@endpush

@php
$etab = session('etablissement');
$membresJson = $membres->map(fn($m) => [
    'id'             => $m->id,
    'nom'            => $m->account ? trim(($m->account->prenom??'').' '.($m->account->nom??'')) : ($m->utilisateur?->mail ?? '—'),
    'initials'       => $m->account
        ? strtoupper(mb_substr($m->account->prenom??'',0,1).mb_substr($m->account->nom??'',0,1))
        : strtoupper(mb_substr($m->utilisateur?->mail??'?',0,2)),
    'mail'           => $m->utilisateur?->mail ?? '—',
    'role'           => $m->role?->libelle ?? '—',
    'id_role'        => $m->id_role,
    'id_utilisateur' => $m->id_utilisateur,
    'actif'          => $m->id_statut == 1,
]);
$usersJson = $users->map(fn($u) => [
    'id'      => $u->id,
    'libelle' => $u->account ? trim(($u->account->prenom??'').' '.($u->account->nom??'')) : $u->mail,
    'sub'     => $u->mail,
]);
$rolesJson = $roles->map(fn($r) => ['id'=>$r->id, 'libelle'=>$r->libelle]);
@endphp

<div x-data="directionPage({{ $membresJson }}, {{ $usersJson }}, {{ $rolesJson }})"
     x-init="$watch('fsDialog', v => { if(v) $nextTick(() => $refs.fsInput && $refs.fsInput.focus()) })"
     class="space-y-5">

    {{-- ── En-tête ──────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold" style="color:#1E293B">Direction</h1>
            <p class="text-sm mt-0.5" style="color:#64748B">
                <span x-text="items.length"></span> membre(s) de direction
            </p>
        </div>
        <button @click="openCreate()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-white text-sm font-semibold hover:opacity-90"
                style="background:var(--primary)">
            <i class="ri-add-line text-base"></i>
            Ajouter Membre
        </button>
    </div>

    {{-- ── Flash ────────────────────────────────────────────── --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
         style="background:rgba(34,197,94,.1); color:#15803d; border:1px solid rgba(34,197,94,.2)">
        <i class="ri-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
         style="background:rgba(239,68,68,.1); color:#dc2626; border:1px solid rgba(239,68,68,.2)">
        <i class="ri-error-warning-fill"></i> {{ session('error') }}
    </div>
    @endif

    {{-- ── DataTable ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100">
            <div class="relative flex-1 max-w-xs">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input x-model="search" @input="currentPage=1" type="text" placeholder="Rechercher..."
                       class="f-input" style="padding:8px 12px 8px 36px">
            </div>
            <div class="flex items-center gap-2 text-sm" style="color:#64748B">
                <span>Lignes :</span>
                <select x-model.number="perPage" @change="currentPage=1"
                        class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm bg-white outline-none">
                    <option>10</option><option>25</option><option>50</option><option>100</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead style="background:#F8FAFC">
                    <tr class="border-b border-gray-100">
                        <th class="tbl-th">UTILISATEUR</th>
                        <th class="tbl-th">ÉTABLISSEMENT</th>
                        <th class="tbl-th">RÔLE</th>
                        <th class="tbl-th text-center">STATUT</th>
                        <th class="tbl-th text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="paginated.length === 0">
                        <tr>
                            <td colspan="5" class="py-16 text-center" style="color:#94A3B8">
                                <i class="ri-user-line block text-5xl mb-3"></i>
                                <p class="text-sm font-semibold" style="color:#64748B">Aucun membre de direction</p>
                            </td>
                        </tr>
                    </template>
                    <template x-for="m in paginated" :key="m.id">
                        <tr class="border-b border-gray-50 hover:bg-slate-50 transition-colors">
                            <td class="tbl-td">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                         style="background:var(--primary)">
                                        <span x-text="m.initials"></span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-[13px]" style="color:#1E293B" x-text="m.nom"></p>
                                        <p class="font-mono text-xs mt-0.5" style="color:#64748B" x-text="m.mail"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="tbl-td">
                                <span style="color:#475569">{{ $etab['nom'] ?? '—' }}</span>
                            </td>
                            <td class="tbl-td">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[11px] font-bold"
                                      style="background:rgba(90,103,216,.1); color:var(--primary)"
                                      x-text="m.role"></span>
                            </td>
                            <td class="tbl-td text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold"
                                      :style="m.actif ? 'background:rgba(34,197,94,.12); color:#15803d' : 'background:rgba(239,68,68,.12); color:#dc2626'">
                                    <i :class="m.actif ? 'ri-check-line' : 'ri-close-line'" class="text-[10px]"></i>
                                    <span x-text="m.actif ? 'Actif' : 'Inactif'"></span>
                                </span>
                            </td>
                            <td class="tbl-td">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEdit(m)" class="action-btn hover:bg-indigo-50" style="color:var(--primary)">
                                        <i class="ri-edit-2-line text-base"></i>
                                    </button>
                                    <form :action="'/direction/'+m.id" method="POST"
                                          @submit.prevent="if(confirm('Retirer ce membre ?')) $el.submit()">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn hover:bg-red-50" style="color:#ef4444">
                                            <i class="ri-delete-bin-2-line text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
            <span class="text-xs" style="color:#64748B" x-text="paginationInfo"></span>
            <div class="flex items-center gap-1">
                <button @click="prevPage()" :disabled="currentPage===1" class="page-btn text-gray-400 hover:bg-gray-100 disabled:opacity-30"><i class="ri-arrow-left-s-line"></i></button>
                <template x-for="p in pageNumbers" :key="p">
                    <button @click="currentPage=p" class="page-btn"
                            :style="currentPage===p ? 'background:var(--primary); color:#fff' : 'color:#475569'"
                            :class="currentPage===p ? '' : 'hover:bg-gray-100'" x-text="p"></button>
                </template>
                <button @click="nextPage()" :disabled="currentPage===totalPages" class="page-btn text-gray-400 hover:bg-gray-100 disabled:opacity-30"><i class="ri-arrow-right-s-line"></i></button>
            </div>
        </div>
    </div>

    {{-- ══ MODAL FORMULAIRE ════════════════════════════════════ --}}
    <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.45)"
         x-transition:enter="transition duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-2xl shadow-2xl w-full" style="max-width:480px" @click.stop>

            <div class="flex items-center gap-3 px-6 py-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:rgba(90,103,216,.12)">
                    <i class="ri-team-line text-lg" style="color:var(--primary)"></i>
                </div>
                <h2 class="flex-1 text-[15px] font-bold" style="color:#1E293B"
                    x-text="editing ? 'Modifier le membre' : 'Ajouter un membre'"></h2>
                <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100">
                    <i class="ri-close-line text-lg" style="color:#64748B"></i>
                </button>
            </div>
            <div class="border-t border-gray-100"></div>

            <form :action="editing ? '/direction/'+form.id : '{{ route('direction.store') }}'" method="POST">
                @csrf
                <template x-if="editing">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="px-6 py-5 space-y-4">

                    {{-- Utilisateur (création seulement) — fsearch dialog --}}
                    <div x-show="!editing">
                        <label class="f-label">Utilisateur <span style="color:#EF4444">*</span></label>
                        <input type="hidden" name="id_utilisateur" :value="form.id_utilisateur">
                        <div class="fs-trigger"
                             @click="openFsDialog('id_utilisateur', 'Sélectionner — Utilisateur', usersData)">
                            <i class="ri-search-line text-sm flex-shrink-0" style="color:#94A3B8"></i>
                            <span class="flex-1 text-sm"
                                  :style="form.id_utilisateur ? 'color:#1E293B' : 'color:#94A3B8'"
                                  x-text="getLabel('id_utilisateur', usersData) || '— Sélectionner un utilisateur —'"></span>
                            <i class="ri-arrow-down-s-line text-sm flex-shrink-0" style="color:#94A3B8"></i>
                        </div>
                    </div>

                    {{-- Rôle — fsearch dialog --}}
                    <div>
                        <label class="f-label">Rôle <span style="color:#EF4444">*</span></label>
                        <input type="hidden" name="id_role" :value="form.id_role">
                        <div class="fs-trigger"
                             @click="openFsDialog('id_role', 'Sélectionner — Rôle', rolesData)">
                            <i class="ri-search-line text-sm flex-shrink-0" style="color:#94A3B8"></i>
                            <span class="flex-1 text-sm"
                                  :style="form.id_role ? 'color:#1E293B' : 'color:#94A3B8'"
                                  x-text="getLabel('id_role', rolesData) || '— Sélectionner un rôle —'"></span>
                            <i class="ri-arrow-down-s-line text-sm flex-shrink-0" style="color:#94A3B8"></i>
                        </div>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-4 px-6 py-4">
                    <button type="button" @click="modal=false"
                            class="text-sm font-semibold hover:opacity-70" style="color:var(--primary)">Annuler</button>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90"
                            style="background:var(--primary)"
                            x-text="editing ? 'Enregistrer' : 'Ajouter'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ DIALOG FSEARCH ══════════════════════════════════════ --}}
    <div x-show="fsDialog" x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center p-6"
         style="background:rgba(0,0,0,.35)"
         x-transition:enter="transition duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="bg-white rounded-2xl shadow-2xl flex flex-col" style="width:440px; max-height:75vh" @click.stop>

            <div class="px-6 py-4">
                <h3 class="text-[15px] font-bold" style="color:#1E293B" x-text="fsTitle"></h3>
            </div>

            <div class="px-4 pb-3">
                <div class="relative">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#94A3B8"></i>
                    <input x-ref="fsInput" x-model="fsSearch"
                           type="text" placeholder="Rechercher..."
                           class="f-input" style="padding-left:34px">
                </div>
            </div>

            <div class="overflow-y-auto flex-1 border-t border-gray-100">
                <template x-for="item in fsFiltered" :key="item.id">
                    <div @click="pickFs(item)"
                         class="fs-item"
                         :class="String(item.id)===String(form[fsField]) ? 'active' : ''">
                        <div>
                            <span x-text="item.libelle"></span>
                            <span x-show="item.sub" class="ml-2 text-xs font-mono" style="color:#94A3B8" x-text="item.sub"></span>
                        </div>
                        <i class="ri-check-line text-base flex-shrink-0"
                           x-show="String(item.id)===String(form[fsField])"
                           style="color:var(--primary)"></i>
                    </div>
                </template>
                <div x-show="fsFiltered.length===0" class="py-10 text-center text-sm" style="color:#94A3B8">
                    Aucun résultat
                </div>
            </div>

            <div class="flex justify-end px-6 py-4 border-t border-gray-100">
                <button @click="fsDialog=false"
                        class="text-sm font-semibold hover:opacity-70" style="color:var(--primary)">Annuler</button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function directionPage(membres, users, roles) {
    return {
        items: membres,
        usersData: users,
        rolesData: roles,

        modal: false, editing: false,
        search: '', perPage: 10, currentPage: 1,
        form: { id:'', id_utilisateur:'', id_role:'' },

        /* ── fsearch dialog partagé ── */
        fsDialog: false,
        fsTitle:  '',
        fsSearch: '',
        fsItems:  [],
        fsField:  '',
        get fsFiltered() {
            if (!this.fsSearch) return this.fsItems;
            const q = this.fsSearch.toLowerCase();
            return this.fsItems.filter(i =>
                i.libelle.toLowerCase().includes(q)
                || (i.sub && i.sub.toLowerCase().includes(q))
            );
        },
        openFsDialog(field, title, items) {
            this.fsField  = field;
            this.fsTitle  = title;
            this.fsItems  = items;
            this.fsSearch = '';
            this.fsDialog = true;
        },
        pickFs(item) {
            this.form[this.fsField] = item.id;
            this.fsDialog = false;
        },
        getLabel(field, items) {
            const val = this.form[field];
            if (!val && val !== 0) return '';
            const item = items.find(i => String(i.id) === String(val));
            return item ? item.libelle : '';
        },

        /* ── pagination ── */
        get filtered() {
            const q = this.search.toLowerCase();
            return !q ? this.items : this.items.filter(m =>
                m.nom.toLowerCase().includes(q) ||
                m.mail.toLowerCase().includes(q) ||
                m.role.toLowerCase().includes(q)
            );
        },
        get paginated() {
            const s = (this.currentPage - 1) * this.perPage;
            return this.filtered.slice(s, s + this.perPage);
        },
        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.perPage)); },
        get pageNumbers() {
            const p = [], t = this.totalPages, c = this.currentPage;
            for (let i = Math.max(1, c-2); i <= Math.min(t, c+2); i++) p.push(i);
            return p;
        },
        get paginationInfo() {
            if (!this.filtered.length) return '0 résultat(s)';
            const s = (this.currentPage - 1) * this.perPage + 1;
            const e = Math.min(this.currentPage * this.perPage, this.filtered.length);
            return `${s}–${e} sur ${this.filtered.length} résultat(s)`;
        },
        prevPage() { if (this.currentPage > 1) this.currentPage--; },
        nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },

        /* ── actions ── */
        openCreate() {
            this.editing = false;
            this.form = { id:'', id_utilisateur:'', id_role:'' };
            this.fsDialog = false;
            this.modal = true;
        },
        openEdit(m) {
            this.editing = true;
            this.form = { ...m };
            this.fsDialog = false;
            this.modal = true;
        },
    }
}
</script>
@endpush

</x-app-layout>
