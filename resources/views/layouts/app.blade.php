<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'TalentSys ERP' }}</title>

    {{-- Tailwind CSS (local) --}}
    <script src="/js/tailwind.cdn.js"></script>
    {{-- Alpine.js (local) --}}
    <script defer src="/js/alpine.min.js"></script>
    {{-- Remix Icons (local) --}}
    <link href="/fonts/remixicon/remixicon.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '{{ $primaryColor ?? "#5A67D8" }}',
                    }
                }
            }
        }
    </script>

    <style>
        :root { --primary: {{ $primaryColor ?? '#5A67D8' }}; }
        .sidebar-bg   { background-color: var(--primary); }

        .sidebar-item {
            display: flex; align-items: center; gap: 12px;
            padding: 9px 12px; border-radius: 10px;
            color: rgba(255,255,255,0.65); font-size: 14px;
            cursor: pointer; transition: all 0.15s ease; text-decoration: none;
            width: 100%;
        }
        .sidebar-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .sidebar-item.active {
            background: rgba(255,255,255,0.18) !important;
            color: white !important;
            font-weight: 700;
        }
        .sidebar-item.active i { color: white !important; }

        .sub-item {
            display: flex; align-items: center; gap: 10px;
            padding: 7px 12px 7px 40px; border-radius: 8px;
            color: rgba(255,255,255,0.55); font-size: 13px;
            cursor: pointer; transition: all 0.15s ease; text-decoration: none;
        }
        .sub-item:hover { background: rgba(255,255,255,0.08); color: white; }
        .sub-item.active { background: rgba(255,255,255,0.12); color: white; font-weight: 600; }

        [x-cloak] { display: none !important; }

        /* Searchable Select */
        .ss-drop{position:absolute;z-index:50;width:100%;top:calc(100% + 2px);background:#fff;border:1.5px solid #E2E8F0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.1);max-height:190px;overflow-y:auto}
        .ss-item{padding:9px 12px;cursor:pointer;font-size:13px;color:#1E293B;transition:background .1s}
        .ss-item:hover{background:#F8FAFC}
        .ss-item.ss-sel{color:var(--primary);font-weight:600;background:rgba(90,103,216,.06)}
    </style>

    @stack('styles')
</head>
<body class="bg-slate-100 font-sans antialiased" x-data="talentsys()" x-cloak>

<div class="flex h-screen overflow-hidden">

    {{-- ── Overlay mobile ────────────────────────────────────────── --}}
    <div class="fixed inset-0 bg-black/50 z-20 md:hidden"
         x-show="sidebarOpen"
         x-transition:enter="transition-opacity duration-200"
         x-transition:leave="transition-opacity duration-200"
         @click="sidebarOpen = false">
    </div>

    {{-- ── SIDEBAR ─────────────────────────────────────────────────── --}}
    <aside class="sidebar-bg flex flex-col z-30 w-64 flex-shrink-0 h-full
                  fixed md:relative
                  -translate-x-full md:translate-x-0 transition-transform duration-200"
           :class="{ 'translate-x-0': sidebarOpen }">

        {{-- Logo & établissement --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
            @php $etab = session('etablissement'); @endphp
            @if(!empty($etab['logo']))
                <img src="{{ $etab['logo'] }}" class="w-10 h-10 rounded-lg object-cover" alt="logo">
            @else
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="ri-graduation-cap-fill text-white text-xl"></i>
                </div>
            @endif
            <div class="overflow-hidden">
                <p class="text-white font-bold text-base leading-tight truncate">
                    {{ $etab['code'] ?? 'UTA' }}
                </p>
                <p class="text-white/60 text-[11px] truncate">
                    {{ $etab['nom'] ?? 'Établissement' }}
                </p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5">
            @include('layouts.partials.sidebar-menu')
        </nav>

        {{-- Changer d'établissement --}}
        <div class="border-t border-white/10 px-4 py-2">
            <a href="{{ route('etablissement.change') }}"
               class="flex items-center gap-2 text-white/40 text-[11px] hover:text-white/70 transition-colors">
                <i class="ri-swap-horizontal-line text-sm"></i>
                Changer d'établissement
            </a>
        </div>

        {{-- Profil utilisateur --}}
        <div class="border-t border-white/10 px-4 py-3 flex items-center gap-3">
            @php
                $account = Auth::user()?->account;
                $initials = $account
                    ? strtoupper(mb_substr($account->prenom ?? '', 0, 1) . mb_substr($account->nom ?? '', 0, 1))
                    : strtoupper(mb_substr(Auth::user()?->mail ?? 'U', 0, 1));
                $fullName = $account ? trim("{$account->prenom} {$account->nom}") : (Auth::user()?->mail ?? 'Utilisateur');
            @endphp
            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                <span class="text-white text-xs font-bold">{{ $initials }}</span>
            </div>
            <div class="flex-1 overflow-hidden">
                <p class="text-white text-[13px] font-semibold truncate">{{ $fullName }}</p>
                <p class="text-white/60 text-[11px] truncate">
                    {{ Auth::user()?->role?->libelle ?? 'Utilisateur' }}
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Déconnexion"
                        class="text-white/50 hover:text-red-300 transition-colors">
                    <i class="ri-logout-box-r-line text-lg"></i>
                </button>
            </form>
        </div>
    </aside>

    {{-- ── CONTENU PRINCIPAL ───────────────────────────────────────── --}}
    <div class="flex flex-col flex-1 overflow-hidden">

        {{-- Topbar --}}
        <header class="bg-white shadow-sm z-10 flex items-center gap-4 px-4 md:px-6 h-16 flex-shrink-0">

            {{-- Bouton menu mobile --}}
            <button class="md:hidden text-gray-500 hover:text-gray-700"
                    @click="sidebarOpen = !sidebarOpen">
                <i class="ri-menu-line text-2xl"></i>
            </button>

            {{-- Recherche --}}
            <div class="hidden md:flex items-center gap-2 bg-slate-100 rounded-full px-4 py-2 w-56">
                <i class="ri-search-line text-gray-400 text-sm"></i>
                <span class="text-gray-400 text-sm">Rechercher...</span>
            </div>

            <div class="flex-1"></div>

            {{-- Pill établissement --}}
            <div class="hidden md:flex items-center gap-2 border border-gray-200 rounded-full px-3 py-1.5">
                <i class="ri-building-line text-gray-500 text-sm"></i>
                <span class="text-sm font-medium text-gray-700 max-w-[180px] truncate">
                    {{ session('etablissement.nom') ?? 'Établissement' }}
                </span>
            </div>

            {{-- Sélecteur année scolaire --}}
            <div x-data="{ open: false }" class="relative">
                @php
                    try {
                        $annees = \App\Models\AnneeScolaire::where('id_etablissement', session('etablissement_id'))->get();
                        $anneeActive = $annees->firstWhere('active', true) ?? $annees->first();
                    } catch (\Throwable $e) {
                        $annees = collect();
                        $anneeActive = null;
                    }
                @endphp
                <button @click="open = !open"
                        class="flex items-center gap-1.5 border rounded-full px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="open ? 'border-green-300 bg-green-50 text-green-800' : 'border-gray-200 bg-white text-gray-700'">
                    <i class="ri-calendar-line text-sm {{ $anneeActive ? 'text-green-600' : 'text-gray-500' }}"></i>
                    {{ $anneeActive?->libelle ?? '—' }}
                    <i class="ri-arrow-down-s-line text-sm"></i>
                </button>
                <div x-show="open" @click.outside="open = false"
                     class="absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 min-w-[180px] py-1">
                    @foreach($annees as $a)
                    <button class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50 text-left"
                            @click="open = false">
                        <i class="ri-circle-fill text-[8px] {{ $a->active ? 'text-green-500' : 'text-gray-300' }}"></i>
                        {{ $a->libelle }}
                        @if($a->active)
                            <span class="ml-auto text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">Active</span>
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Notifications --}}
            <button class="text-gray-400 hover:text-gray-600 relative">
                <i class="ri-notification-3-line text-xl"></i>
            </button>

            {{-- Déconnexion desktop --}}
            <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                @csrf
                <button type="submit"
                        class="flex items-center gap-1.5 text-red-400 hover:text-red-600 text-sm font-medium transition-colors">
                    <i class="ri-logout-box-r-line"></i>
                    Déconnexion
                </button>
            </form>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto bg-slate-100 p-4 md:p-6">
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
                    <i class="ri-checkbox-circle-line"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
                    <i class="ri-error-warning-line"></i>
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

<script>
function talentsys() {
    return {
        sidebarOpen: false,
        activeMenu: '{{ request()->route()?->getName() ?? "dashboard" }}',
        expandedMenus: [],

        toggleMenu(menu) {
            const idx = this.expandedMenus.indexOf(menu);
            if (idx >= 0) this.expandedMenus.splice(idx, 1);
            else this.expandedMenus.push(menu);
        },
        isExpanded(menu) {
            return this.expandedMenus.includes(menu);
        },
    }
}
</script>

{{-- Global: Searchable Select component --}}
<script>
function sSelect(opts, initVal, ph) {
    return {
        opts: opts || [], ph: ph || 'Rechercher...', v: initVal != null ? String(initVal) : '', s: '', open: false,
        get filtered() { const q=this.s.toLowerCase(); return q?this.opts.filter(o=>o.l.toLowerCase().includes(q)):this.opts; },
        select(o) { this.v=String(o.v); this.s=o.l; this.open=false; },
        init() { if(this.v){const f=this.opts.find(o=>String(o.v)===this.v); if(f)this.s=f.l;} }
    }
}
</script>

@stack('scripts')
</body>
</html>
