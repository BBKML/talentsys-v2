<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentSys — Choisir un établissement</title>
    <script src="/js/tailwind.cdn.js"></script>
    <link href="/fonts/remixicon/remixicon.css" rel="stylesheet">
    <script defer src="/js/alpine.min.js"></script>
</head>
<body class="min-h-screen bg-gray-50">

{{-- ── Barre supérieure ──────────────────────────────────────────────── --}}
<header class="bg-white border-b border-gray-100 px-6 py-3.5 flex items-center justify-between sticky top-0 z-10">
    <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl overflow-hidden shadow-sm">
            <img src="/images/logo_talentsys.jpeg" alt="TalentSys" class="w-full h-full object-cover">
        </div>
        <span class="font-bold text-gray-900 text-lg">TalentSys</span>
    </div>

    @php
        $account = Auth::user()?->account;
        $displayName = $account
            ? trim(($account->prenom ?? '') . ' ' . ($account->nom ?? ''))
            : (Auth::user()?->mail ?? 'Utilisateur');
        $prenom = $account?->prenom ?? explode('@', Auth::user()?->mail ?? 'Utilisateur')[0];
        $initials = $account
            ? strtoupper(mb_substr($account->prenom ?? '', 0, 1) . mb_substr($account->nom ?? '', 0, 1))
            : strtoupper(mb_substr(Auth::user()?->mail ?? 'U', 0, 2));
    @endphp

    <div class="flex items-center gap-3">
        <div class="text-right hidden sm:block">
            <p class="text-sm font-semibold text-gray-800">{{ $displayName }}</p>
            <p class="text-xs text-gray-400">{{ Auth::user()?->role?->libelle ?? 'Utilisateur' }}</p>
        </div>
        <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center">
            <span class="text-indigo-700 text-sm font-bold">{{ $initials }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors" title="Se déconnecter">
                <i class="ri-logout-box-r-line text-xl"></i>
            </button>
        </form>
    </div>
</header>

{{-- ── Contenu ───────────────────────────────────────────────────────── --}}
<div class="min-h-[calc(100vh-57px)] flex flex-col items-center justify-center px-4 py-10">
<div class="w-full max-w-2xl">

    {{-- Greeting --}}
    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            Bonjour, {{ strtoupper($prenom) }}
        </h1>
        <p class="text-gray-500 text-sm">
            Sélectionnez l'établissement auquel vous souhaitez accéder.
        </p>
    </div>

    {{-- Erreur --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 flex items-center gap-2 text-sm">
        <i class="ri-error-warning-line text-lg"></i>
        {{ $errors->first() }}
    </div>
    @endif

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-6 text-sm">
        <i class="ri-checkbox-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Grille des établissements --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($etablissements as $etab)
        @php $logoUrl = $etab->logo ? \Illuminate\Support\Facades\Storage::url($etab->logo) : null; @endphp

        <div x-data="{ uploading: false }" class="relative">

            {{-- Formulaire upload logo (caché) --}}
            <form method="POST" action="{{ route('etablissement.logo.byId', $etab->id) }}"
                  enctype="multipart/form-data" x-ref="lf{{ $etab->id }}" style="display:none">
                @csrf
                <input type="file" name="logo" accept="image/*"
                       x-ref="li{{ $etab->id }}"
                       @change="$refs['lf{{ $etab->id }}'].submit()">
            </form>

            {{-- Carte sélection --}}
            <form method="POST" action="{{ route('etablissement.store') }}">
                @csrf
                <input type="hidden" name="etablissement_id" value="{{ $etab->id }}">
                <button type="submit"
                        class="w-full text-left p-5 bg-white rounded-2xl border-2 transition-all duration-150
                               hover:shadow-lg hover:-translate-y-0.5 group
                               {{ $etab->siege ? 'border-yellow-400 shadow-yellow-50 shadow-md' : 'border-gray-100 hover:border-gray-200' }}">

                    {{-- Ligne supérieure : logo + badge + icônes --}}
                    <div class="flex items-start justify-between mb-4">
                        {{-- Logo cliquable --}}
                        <div class="relative flex-shrink-0">
                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 flex items-center justify-center border border-gray-100">
                                @if($logoUrl)
                                    <img src="{{ $logoUrl }}" class="w-12 h-12 object-cover" alt="{{ $etab->code }}">
                                @else
                                    <div class="w-full h-full bg-indigo-100 flex items-center justify-center">
                                        <i class="ri-building-2-fill text-indigo-400 text-2xl"></i>
                                    </div>
                                @endif
                            </div>
                            {{-- Badge caméra --}}
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center shadow cursor-pointer"
                                 style="background:#5A67D8"
                                 @click.stop="$refs['li{{ $etab->id }}'].click()"
                                 title="Changer le logo">
                                <i class="ri-camera-fill text-white" style="font-size:9px"></i>
                            </div>
                        </div>

                        {{-- Badges + icônes --}}
                        <div class="flex items-center gap-2">
                            @if($etab->siege)
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-yellow-50 text-yellow-700
                                         px-2.5 py-1 rounded-full border border-yellow-300">
                                <i class="ri-star-fill text-yellow-500 text-[10px]"></i>
                                Siège
                            </span>
                            @endif
                            <div class="flex items-center gap-1 text-gray-300 group-hover:text-indigo-400 transition-colors">
                                <i class="ri-edit-line text-base"></i>
                                <i class="ri-arrow-right-line text-base"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Nom --}}
                    <p class="font-bold text-gray-900 text-[15px] mb-1 leading-snug">{{ $etab->nom }}</p>

                    {{-- Localisation --}}
                    @php
                        $location = implode(' • ', array_filter([
                            $etab->commune ?? $etab->ville ?? null,
                            $etab->systeme_academique ? 'Système ' . $etab->systeme_academique : null,
                        ]));
                    @endphp
                    @if($location)
                    <p class="text-xs text-gray-400 mb-3">{{ $location }}</p>
                    @endif

                    {{-- Code --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[11px] font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded tracking-wide">
                            {{ $etab->code }}
                        </span>
                        @php $mail = $etab->email_1 ?? $etab->mail ?? $etab->email ?? null; @endphp
                        @if($mail)
                        <span class="text-[11px] text-gray-400">{{ $mail }}</span>
                        @endif
                    </div>
                </button>
            </form>
        </div>
        @endforeach
    </div>

    {{-- Ajouter un établissement --}}
    @if(Auth::user()?->id_role == 8 || Auth::user()?->role?->is_super_admin)
    <div class="text-center mt-8">
        <a href="#"
           class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
            <i class="ri-add-circle-line text-base"></i>
            Ajouter un établissement
        </a>
    </div>
    @endif

</div>
</div>

</body>
</html>
