<x-app-layout title="Couleurs — Établissement">

@push('styles')
<style>
.tbl-th { font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase; letter-spacing:.06em; padding:12px 16px; text-align:left; }
.tbl-td { padding:12px 16px; font-size:13px; color:#475569; }
.action-btn { width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; transition:all .15s; cursor:pointer; border:none; background:transparent; }
.page-btn { width:32px; height:32px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; transition:all .15s; }
.f-label { font-size:12px; font-weight:600; color:#475569; margin-bottom:6px; display:block; }
.f-input  { width:100%; padding:10px 12px; background:#F1F5F9; border:none; border-radius:8px; font-size:13px; color:#1E293B; outline:none; transition:all .15s; }
.f-input:focus { background:#fff; box-shadow:0 0 0 2px var(--primary)44; }
/* Palette */
.pal-cell { width:30px; height:30px; border-radius:6px; cursor:pointer; flex-shrink:0; position:relative; transition:transform .1s; border:2px solid transparent; }
.pal-cell:hover { transform:scale(1.15); z-index:2; }
.pal-cell.selected { border-color:#fff; box-shadow:0 0 0 2px #475569; }
</style>
@endpush

@php
$rolesInfo = [
    'primary'   => ['label'=>'Primary — Couleur Principale',  'desc'=>'Barre latérale (sidebar), boutons principaux, liens actifs, icônes de menu, en-têtes de formulaires', 'icon'=>'ri-star-fill'],
    'accent'    => ['label'=>'Accent — Couleur Secondaire',    'desc'=>'Badges, graphiques du tableau de bord, indicateurs de statut, éléments de mise en avant',           'icon'=>'ri-sparkling-2-fill'],
    'secondary' => ['label'=>'Secondary — Couleur Secondaire', 'desc'=>'Éléments secondaires de l\'interface',                                                                 'icon'=>'ri-contrast-2-fill'],
    'success'   => ['label'=>'Success — Succès',               'desc'=>'Messages de succès, confirmations, actions validées',                                                  'icon'=>'ri-checkbox-circle-fill'],
    'warning'   => ['label'=>'Warning — Avertissement',        'desc'=>'Messages d\'avertissement, alertes, états de prudence',                                                'icon'=>'ri-error-warning-fill'],
    'danger'    => ['label'=>'Danger — Erreur',                'desc'=>'Messages d\'erreur, suppressions, états critiques',                                                    'icon'=>'ri-close-circle-fill'],
];
$cleOptions = [
    ''          => 'Sans rôle',
    'primary'   => 'Primary (Principale)',
    'accent'    => 'Accent (Secondaire)',
    'secondary' => 'Secondary',
    'success'   => 'Success (Succès)',
    'warning'   => 'Warning (Avertissement)',
    'danger'    => 'Danger (Erreur)',
];
$couleursJson = $couleurs->map(fn($c)=>[
    'id'      => $c->id,
    'libelle' => $c->libelle ?? '',
    'cle'     => $c->cle ?? '',
    'code_hex'=> $c->code_hex ?? '#5A67D8',
]);
// Résumé : couleurs avec un rôle connu
$couleursResume = $couleurs->filter(fn($c) => isset($rolesInfo[$c->cle ?? '']))->values();
@endphp

<div x-data="couleursPage({{ $couleursJson }})" class="space-y-5">

    {{-- ── En-tête ── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold" style="color:#1E293B">Couleurs</h1>
            <p class="text-sm mt-0.5" style="color:#64748B">Personnalisez les couleurs de l'interface</p>
        </div>
        <button @click="openCreate()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-white text-sm font-semibold hover:opacity-90"
                style="background:var(--primary)">
            <i class="ri-add-line text-base"></i>
            Nouvelle Couleur
        </button>
    </div>

    {{-- ── Flash ── --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
         style="background:rgba(34,197,94,.1); color:#15803d; border:1px solid rgba(34,197,94,.2)">
        <i class="ri-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    {{-- ── Résumé des rôles ── --}}
    @if($couleursResume->count() > 0)
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <p class="text-[12px] font-bold mb-3" style="color:#64748B; text-transform:uppercase; letter-spacing:.06em">Rôles des couleurs</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($couleursResume as $c)
            @php $info = $rolesInfo[$c->cle] ?? null; @endphp
            @if($info)
            <div class="flex items-start gap-3 p-3 rounded-xl" style="background:{{ $c->code_hex }}18; border:1px solid {{ $c->code_hex }}33">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:{{ $c->code_hex }}">
                    <i class="{{ $info['icon'] }} text-white text-base"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] font-bold" style="color:#1E293B">{{ $info['label'] }}</p>
                    <p class="text-[11px] mt-0.5 leading-relaxed" style="color:#64748B">{{ $info['desc'] }}</p>
                    <p class="text-[12px] font-bold mt-1 font-mono" style="color:{{ $c->code_hex }}">{{ strtoupper($c->code_hex) }}</p>
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── DataTable ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        {{-- Barre recherche --}}
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100">
            <div class="relative flex-1 max-w-xs">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input x-model="search" @input="page=1" type="text" placeholder="Rechercher..."
                       class="f-input" style="padding:8px 12px 8px 36px">
            </div>
            <div class="flex items-center gap-2 text-sm" style="color:#64748B">
                <span>Lignes/page :</span>
                <select x-model.number="perPage" @change="page=1"
                        class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm bg-white outline-none">
                    <option>10</option><option>25</option><option>50</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead style="background:#F8FAFC">
                    <tr class="border-b border-gray-100">
                        <th class="tbl-th" style="width:80px">APERÇU</th>
                        <th class="tbl-th" style="width:130px">CODE HEX</th>
                        <th class="tbl-th">LIBELLÉ</th>
                        <th class="tbl-th" style="width:160px">RÔLE</th>
                        <th class="tbl-th text-right" style="width:80px">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="paginated.length===0">
                        <tr><td colspan="5" class="py-16 text-center" style="color:#94A3B8">
                            <i class="ri-palette-line block text-5xl mb-3"></i>
                            <p class="text-sm font-semibold" style="color:#64748B">Aucune couleur enregistrée</p>
                        </td></tr>
                    </template>
                    <template x-for="c in paginated" :key="c.id">
                        <tr class="border-b border-gray-50 hover:bg-slate-50 transition-colors">
                            <td class="tbl-td">
                                <div class="w-10 h-10 rounded-xl shadow-sm"
                                     :style="'background:' + c.code_hex"></div>
                            </td>
                            <td class="tbl-td">
                                <span class="font-mono text-[13px] font-semibold" style="color:#1E293B"
                                      x-text="c.code_hex.toUpperCase()"></span>
                            </td>
                            <td class="tbl-td" style="color:#475569" x-text="c.libelle || '—'"></td>
                            <td class="tbl-td">
                                <template x-if="c.cle === 'primary'">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold"
                                          :style="'background:'+c.code_hex+'22; color:'+c.code_hex">
                                        <i class="ri-star-fill text-[10px]"></i> Principale
                                    </span>
                                </template>
                                <template x-if="c.cle === 'accent'">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold"
                                          style="background:rgba(13,148,136,.12); color:#0D9488">
                                        <i class="ri-circle-fill text-[8px]"></i> Accent
                                    </span>
                                </template>
                                <template x-if="c.cle && c.cle !== 'primary' && c.cle !== 'accent'">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold"
                                          style="background:#F1F5F9; color:#64748B"
                                          x-text="c.cle"></span>
                                </template>
                                <template x-if="!c.cle">
                                    <span class="text-[12px]" style="color:#CBD5E1">—</span>
                                </template>
                            </td>
                            <td class="tbl-td">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEdit(c)" class="action-btn hover:bg-indigo-50" style="color:var(--primary)">
                                        <i class="ri-edit-2-line text-base"></i>
                                    </button>
                                    <form :action="'{{ url('/etablissement-couleurs') }}/' + c.id" method="POST"
                                          style="display:inline"
                                          @submit.prevent="if(confirm('Supprimer cette couleur ?')) $el.submit()">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn hover:bg-red-50" style="color:#EF4444">
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

        {{-- Pagination --}}
        <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
            <span class="text-xs" style="color:#64748B" x-text="info"></span>
            <div class="flex items-center gap-1">
                <button @click="page--" :disabled="page===1" class="page-btn text-gray-400 hover:bg-gray-100 disabled:opacity-30"><i class="ri-arrow-left-s-line"></i></button>
                <template x-for="p in pages" :key="p">
                    <button @click="page=p" class="page-btn"
                            :style="page===p ? 'background:var(--primary); color:#fff' : 'color:#475569'"
                            :class="page===p ? '' : 'hover:bg-gray-100'" x-text="p"></button>
                </template>
                <button @click="page++" :disabled="page===totalPages" class="page-btn text-gray-400 hover:bg-gray-100 disabled:opacity-30"><i class="ri-arrow-right-s-line"></i></button>
            </div>
        </div>
    </div>

    {{-- ══ MODAL ══ --}}
    <div x-show="modal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.45)">
        <div class="bg-white rounded-2xl shadow-2xl w-full"
             style="max-width:520px; max-height:92vh; overflow-y:auto"
             @click.stop>

            {{-- Header --}}
            <div class="flex items-center gap-3 px-6 py-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:rgba(90,103,216,.12)">
                    <i class="ri-palette-fill text-lg" style="color:var(--primary)"></i>
                </div>
                <h2 class="flex-1 text-[15px] font-bold" style="color:#1E293B"
                    x-text="editing ? 'Modifier la couleur' : 'Nouvelle Couleur'"></h2>
                <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100">
                    <i class="ri-close-line text-lg" style="color:#64748B"></i>
                </button>
            </div>
            <div class="border-t border-gray-100"></div>

            <form :action="editing ? '{{ url('/etablissement-couleurs') }}/'+form.id : '{{ route('etablissement.couleurs.save') }}'"
                  method="POST" @submit="submitting=true">
                @csrf
                <template x-if="editing">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="couleurs[0][id]"       :value="editing ? form.id : ''">
                <input type="hidden" name="couleurs[0][libelle]"  :value="form.libelle">
                <input type="hidden" name="couleurs[0][cle]"      :value="form.cle">
                <input type="hidden" name="couleurs[0][code_hex]" :value="form.code_hex">

                <div class="px-6 pt-5 pb-2 space-y-4">

                    {{-- Libellé + Rôle --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="f-label">Libellé</label>
                            <input type="text" x-model="form.libelle" class="f-input"
                                   placeholder="Ex: Couleur Principale">
                        </div>
                        <div>
                            <label class="f-label">Rôle</label>
                            <select x-model="form.cle" class="f-input" style="cursor:pointer">
                                @foreach($cleOptions as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Palette de couleurs --}}
                    <div>
                        <label class="f-label">Choisir une couleur</label>
                        <div class="p-3 rounded-xl" style="background:#F8FAFC; border:1px solid #E2E8F0">
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="color in palette" :key="color">
                                    <div class="pal-cell"
                                         :class="form.code_hex.toUpperCase()===color.toUpperCase() ? 'selected' : ''"
                                         :style="'background:' + color"
                                         @click="form.code_hex = color; hexInput = color"
                                         :title="color">
                                        <template x-if="form.code_hex.toUpperCase()===color.toUpperCase()">
                                            <i class="ri-check-line text-white text-xs absolute inset-0 flex items-center justify-center"
                                               style="display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700"></i>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Code HEX personnalisé --}}
                    <div>
                        <label class="f-label">Code HEX personnalisé</label>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl flex-shrink-0 shadow-sm border border-gray-200"
                                 :style="'background:' + form.code_hex"></div>
                            <input type="text" x-model="hexInput"
                                   @input="applyHex()"
                                   class="f-input font-mono uppercase flex-1"
                                   placeholder="#5A67D8" maxlength="7">
                            <button type="button"
                                    @click="form.code_hex = hexInput"
                                    class="px-4 py-2.5 rounded-xl text-sm font-semibold flex-shrink-0"
                                    style="background:var(--primary); color:#fff">
                                Aperçu
                            </button>
                        </div>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-4 px-6 py-4">
                    <button type="button" @click="modal=false"
                            class="text-sm font-semibold hover:opacity-70"
                            style="color:var(--primary)">Annuler</button>
                    <button type="submit" :disabled="submitting"
                            class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60"
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
function couleursPage(data) {
    const PALETTE = [
        '#FFCDD2','#EF9A9A','#E57373','#EF5350','#F44336','#E53935','#D32F2F','#C62828','#B71C1C',
        '#F8BBD0','#F48FB1','#F06292','#EC407A','#E91E63','#D81B60','#C2185B','#AD1457','#880E4F',
        '#E1BEE7','#CE93D8','#BA68C8','#AB47BC','#9C27B0','#8E24AA','#7B1FA2','#6A1B9A','#4A148C',
        '#C5CAE9','#9FA8DA','#7986CB','#5C6BC0','#3F51B5','#3949AB','#303F9F','#283593','#1A237E',
        '#BBDEFB','#90CAF9','#64B5F6','#42A5F5','#2196F3','#1E88E5','#1976D2','#1565C0','#0D47A1',
        '#B3E5FC','#81D4FA','#4FC3F7','#29B6F6','#03A9F4','#039BE5','#0288D1','#0277BD','#01579B',
        '#B2DFDB','#80CBC4','#4DB6AC','#26A69A','#009688','#00897B','#00796B','#00695C','#004D40',
        '#C8E6C9','#A5D6A7','#81C784','#66BB6A','#4CAF50','#43A047','#388E3C','#2E7D32','#1B5E20',
        '#DCEDC8','#C5E1A5','#AED581','#9CCC65','#8BC34A','#7CB342','#689F38','#558B2F','#33691E',
        '#FFF9C4','#FFF59D','#FFF176','#FFEE58','#FFEB3B','#FDD835','#F9A825','#F57F17',
        '#FFE0B2','#FFCC80','#FFB74D','#FFA726','#FF9800','#FB8C00','#F57C00','#E65100',
        '#FFCCBC','#FFAB91','#FF8A65','#FF7043','#FF5722','#F4511E','#E64A19','#BF360C',
        '#D7CCC8','#BCAAA4','#A1887F','#8D6E63','#795548','#6D4C41','#5D4037','#4E342E',
        '#F5F5F5','#EEEEEE','#E0E0E0','#BDBDBD','#9E9E9E','#757575','#616161','#424242','#212121',
        '#CFD8DC','#B0BEC5','#90A4AE','#78909C','#607D8B','#546E7A','#455A64','#37474F','#263238',
        '#FFFFFF','#F8F9FA','#E2E8F0','#64748B','#334155','#1E293B','#0F172A','#000000',
        '#5A67D8','#667EEA','#764BA2','#F093FB','#4FACFE','#00F2FE','#43E97B','#38F9D7',
    ];

    return {
        items: data,
        search: '', page: 1, perPage: 10,
        modal: false, editing: false, submitting: false,
        palette: PALETTE,
        hexInput: '#5A67D8',
        form: { id:'', libelle:'', cle:'', code_hex:'#5A67D8' },

        get filtered() {
            const q = this.search.toLowerCase();
            if (!q) return this.items;
            return this.items.filter(c =>
                (c.libelle||'').toLowerCase().includes(q) ||
                (c.cle||'').toLowerCase().includes(q) ||
                c.code_hex.toLowerCase().includes(q)
            );
        },
        get paginated() {
            const s = (this.page-1)*this.perPage;
            return this.filtered.slice(s, s+this.perPage);
        },
        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length/this.perPage)); },
        get pages() {
            const p=[], t=this.totalPages, c=this.page;
            for(let i=Math.max(1,c-2); i<=Math.min(t,c+2); i++) p.push(i);
            return p;
        },
        get info() {
            if(!this.filtered.length) return '0 résultat(s)';
            const s=(this.page-1)*this.perPage+1;
            const e=Math.min(this.page*this.perPage, this.filtered.length);
            return `${s}–${e} sur ${this.filtered.length} résultat(s)`;
        },

        applyHex() {
            const v = this.hexInput;
            if (/^#[0-9A-Fa-f]{6}$/.test(v)) this.form.code_hex = v;
        },

        openCreate() {
            this.editing = false; this.submitting = false;
            this.form = { id:'', libelle:'', cle:'', code_hex:'#5A67D8' };
            this.hexInput = '#5A67D8';
            this.modal = true;
        },
        openEdit(c) {
            this.editing = true; this.submitting = false;
            this.form = { ...c };
            this.hexInput = c.code_hex;
            this.modal = true;
        },
    }
}
</script>
@endpush

</x-app-layout>
