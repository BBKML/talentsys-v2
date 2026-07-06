<x-app-layout title="Historique — TalentSys">

@push('styles')
<style>
.tbl-th { font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase; letter-spacing:.06em; padding:12px 16px; text-align:left; }
.tbl-td { padding:12px 16px; font-size:13px; color:#475569; }
.page-btn { width:32px; height:32px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; transition:all .15s; }
.kpi-card { background:#fff; border-radius:12px; border:1px solid #E2E8F0; padding:16px; display:flex; align-items:center; gap:12px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
.kpi-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
/* Onglets */
.tab-btn { display:inline-flex; align-items:center; gap:6px; padding:12px 20px; font-size:13px; font-weight:600; border:none; background:transparent; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-1px; transition:all .15s; }
.tab-btn.active { border-bottom-color:var(--primary); color:var(--primary); }
.tab-btn.inactive { color:#64748B; }
.tab-btn.inactive:hover { color:#475569; }
</style>
@endpush

@php
$totalActivites  = $activites->count();
$totalConnexions = $connexions->count();
$enLigne         = $connexions->whereNull('logout')->count();

$activitesJson = $activites->map(fn($a)=>[
    'nom'      => $a->account ? trim(($a->account->prenom??'').' '.($a->account->nom??'')) : 'Système',
    'activite' => $a->activite,
    'date'     => $a->date ? \Carbon\Carbon::parse($a->date)->format('d/m/Y') : '—',
    'heure'    => $a->heure ? substr($a->heure,0,8) : '—',
]);

$connexionsJson = $connexions->map(fn($c)=>[
    'nom'    => $c->nom,
    'mail'   => $c->mail,
    'login'  => $c->login  ? \Carbon\Carbon::parse($c->login)->format('d/m/Y') : '—',
    'loginH' => $c->login  ? \Carbon\Carbon::parse($c->login)->format('H:i:s') : '—',
    'logout' => $c->logout ? \Carbon\Carbon::parse($c->logout)->format('H:i:s') : null,
    'duree'  => ($c->login && $c->logout)
        ? (function() use($c) {
            $s = \Carbon\Carbon::parse($c->login)->diffInSeconds(\Carbon\Carbon::parse($c->logout));
            $h = intdiv($s,3600); $m = intdiv($s%3600,60);
            return $h>0 ? "{$h}h {$m}min" : "{$m}min";
          })()
        : null,
]);
@endphp

<div x-data="historiquePage({{ $activitesJson }}, {{ $connexionsJson }})" class="space-y-5">

    {{-- ── En-tête ──────────────────────────────────────────── --}}
    <div>
        <h1 class="text-2xl font-bold" style="color:#1E293B">Historique</h1>
        <p class="text-sm mt-0.5" style="color:#64748B">Journal d'activités et connexions utilisateurs</p>
    </div>

    {{-- ── KPI Cards ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(90,103,216,.1)">
                <i class="ri-history-line text-lg" style="color:#5A67D8"></i>
            </div>
            <div>
                <p class="text-xl font-bold" style="color:#5A67D8">{{ $totalActivites }}</p>
                <p class="text-[11px] mt-0.5" style="color:#64748B">Activités</p>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(13,148,136,.1)">
                <i class="ri-login-circle-line text-lg" style="color:#0D9488"></i>
            </div>
            <div>
                <p class="text-xl font-bold" style="color:#0D9488">{{ $totalConnexions }}</p>
                <p class="text-[11px] mt-0.5" style="color:#64748B">Connexions</p>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(34,197,94,.1)">
                <i class="ri-circle-fill text-base" style="color:#22C55E"></i>
            </div>
            <div>
                <p class="text-xl font-bold" style="color:#22C55E">{{ $enLigne }}</p>
                <p class="text-[11px] mt-0.5" style="color:#64748B">En ligne</p>
            </div>
        </div>
    </div>

    {{-- ══ CARD UNIQUE : onglets + contenu ═══════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        {{-- ── Barre d'onglets ── --}}
        <div style="border-bottom:1px solid #E2E8F0; padding:0 8px">
            <button @click="tab='activites'"
                    class="tab-btn"
                    :class="tab==='activites' ? 'active' : 'inactive'">
                <i class="ri-history-line text-sm"></i>
                Activités
            </button>
            <button @click="tab='connexions'"
                    class="tab-btn"
                    :class="tab==='connexions' ? 'active' : 'inactive'">
                <i class="ri-login-circle-line text-sm"></i>
                Connexions
            </button>
        </div>

        {{-- ── Barre recherche + lignes/page ── --}}
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100">
            <div class="relative flex-1 max-w-xs">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input x-model="tab==='activites' ? searchA : searchC"
                       @input="tab==='activites' ? pageA=1 : pageC=1"
                       type="text" placeholder="Rechercher..."
                       style="width:100%; padding:8px 12px 8px 36px; background:#F1F5F9; border:none; border-radius:8px; font-size:13px; outline:none">
            </div>
            <div class="flex items-center gap-2 text-sm" style="color:#64748B">
                <span>Lignes/page :</span>
                <select x-model.number="tab==='activites' ? perPageA : perPageC"
                        class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm bg-white outline-none">
                    <option>10</option><option>25</option><option>50</option><option>100</option>
                </select>
            </div>
        </div>

        {{-- ── TAB Activités ── --}}
        <div x-show="tab==='activites'">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead style="background:#F8FAFC">
                        <tr class="border-b border-gray-100">
                            <th class="tbl-th" style="width:120px">DATE</th>
                            <th class="tbl-th" style="width:100px">HEURE</th>
                            <th class="tbl-th">ACTIVITÉ</th>
                            <th class="tbl-th">UTILISATEUR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="paginatedA.length===0">
                            <tr><td colspan="4" class="py-14 text-center" style="color:#94A3B8; font-size:13px">
                                <i class="ri-history-line block text-4xl mb-2"></i>Aucune activité enregistrée
                            </td></tr>
                        </template>
                        <template x-for="(a,i) in paginatedA" :key="i">
                            <tr class="border-b border-gray-50 hover:bg-slate-50 transition-colors">
                                <td class="tbl-td"><span class="font-mono text-xs" style="color:#475569" x-text="a.date"></span></td>
                                <td class="tbl-td"><span class="font-mono text-xs" style="color:#94A3B8" x-text="a.heure"></span></td>
                                <td class="tbl-td">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:var(--primary)"></span>
                                        <span class="text-[13px]" style="color:#1E293B" x-text="a.activite"></span>
                                    </div>
                                </td>
                                <td class="tbl-td" style="color:#475569" x-text="a.nom"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
                <span class="text-xs" style="color:#64748B" x-text="infoA"></span>
                <div class="flex items-center gap-1">
                    <button @click="pageA--" :disabled="pageA===1" class="page-btn text-gray-400 hover:bg-gray-100 disabled:opacity-30"><i class="ri-arrow-left-s-line"></i></button>
                    <template x-for="p in pagesA" :key="p">
                        <button @click="pageA=p" class="page-btn"
                                :style="pageA===p ? 'background:var(--primary); color:#fff' : 'color:#475569'"
                                :class="pageA===p ? '' : 'hover:bg-gray-100'" x-text="p"></button>
                    </template>
                    <button @click="pageA++" :disabled="pageA===totalA" class="page-btn text-gray-400 hover:bg-gray-100 disabled:opacity-30"><i class="ri-arrow-right-s-line"></i></button>
                </div>
            </div>
        </div>

        {{-- ── TAB Connexions ── --}}
        <div x-show="tab==='connexions'">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead style="background:#F8FAFC">
                        <tr class="border-b border-gray-100">
                            <th class="tbl-th">UTILISATEUR</th>
                            <th class="tbl-th" style="width:110px">DATE</th>
                            <th class="tbl-th" style="width:100px">CONNEXION</th>
                            <th class="tbl-th" style="width:110px">DÉCONNEXION</th>
                            <th class="tbl-th" style="width:90px">DURÉE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="paginatedC.length===0">
                            <tr><td colspan="5" class="py-14 text-center" style="color:#94A3B8; font-size:13px">
                                <i class="ri-login-circle-line block text-4xl mb-2"></i>Aucune connexion enregistrée
                            </td></tr>
                        </template>
                        <template x-for="(c,i) in paginatedC" :key="i">
                            <tr class="border-b border-gray-50 hover:bg-slate-50 transition-colors">
                                <td class="tbl-td">
                                    <p class="font-semibold text-[13px]" style="color:#1E293B" x-text="c.nom"></p>
                                    <p class="font-mono text-xs mt-0.5" style="color:#64748B" x-text="c.mail"></p>
                                </td>
                                <td class="tbl-td"><span class="font-mono text-xs" style="color:#475569" x-text="c.login"></span></td>
                                <td class="tbl-td"><span class="font-mono text-sm font-semibold" style="color:#22C55E" x-text="c.loginH"></span></td>
                                <td class="tbl-td">
                                    <template x-if="!c.logout">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold"
                                              style="background:rgba(34,197,94,.12); color:#15803d">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span>
                                            En ligne
                                        </span>
                                    </template>
                                    <template x-if="c.logout">
                                        <span class="font-mono text-sm" style="color:#EF4444" x-text="c.logout"></span>
                                    </template>
                                </td>
                                <td class="tbl-td"><span class="text-[13px]" style="color:#64748B" x-text="c.duree ?? '—'"></span></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
                <span class="text-xs" style="color:#64748B" x-text="infoC"></span>
                <div class="flex items-center gap-1">
                    <button @click="pageC--" :disabled="pageC===1" class="page-btn text-gray-400 hover:bg-gray-100 disabled:opacity-30"><i class="ri-arrow-left-s-line"></i></button>
                    <template x-for="p in pagesC" :key="p">
                        <button @click="pageC=p" class="page-btn"
                                :style="pageC===p ? 'background:var(--primary); color:#fff' : 'color:#475569'"
                                :class="pageC===p ? '' : 'hover:bg-gray-100'" x-text="p"></button>
                    </template>
                    <button @click="pageC++" :disabled="pageC===totalC" class="page-btn text-gray-400 hover:bg-gray-100 disabled:opacity-30"><i class="ri-arrow-right-s-line"></i></button>
                </div>
            </div>
        </div>

    </div>{{-- fin card ──────────────────────────────────────────── --}}

</div>

@push('scripts')
<script>
function historiquePage(activites, connexions) {
    const mkPager = (items, search, page, perPage) => {
        const q = search.toLowerCase();
        const f = !q ? items : items.filter(i => JSON.stringify(i).toLowerCase().includes(q));
        const total = Math.max(1, Math.ceil(f.length / perPage));
        const s = (page - 1) * perPage;
        const paginated = f.slice(s, s + perPage);
        const info = f.length
            ? `${s+1}–${Math.min(page*perPage, f.length)} sur ${f.length} résultat(s)`
            : '0 résultat(s)';
        const pages = [];
        for(let i=Math.max(1,page-2); i<=Math.min(total,page+2); i++) pages.push(i);
        return { paginated, total, pages, info };
    };

    return {
        tab: 'activites',
        activites, connexions,
        searchA: '', pageA: 1, perPageA: 25,
        searchC: '', pageC: 1, perPageC: 25,

        get _pagerA() { return mkPager(this.activites, this.searchA, this.pageA, this.perPageA); },
        get paginatedA() { return this._pagerA.paginated; },
        get totalA()    { return this._pagerA.total; },
        get pagesA()    { return this._pagerA.pages; },
        get infoA()     { return this._pagerA.info; },

        get _pagerC() { return mkPager(this.connexions, this.searchC, this.pageC, this.perPageC); },
        get paginatedC() { return this._pagerC.paginated; },
        get totalC()    { return this._pagerC.total; },
        get pagesC()    { return this._pagerC.pages; },
        get infoC()     { return this._pagerC.info; },
    }
}
</script>
@endpush

</x-app-layout>
