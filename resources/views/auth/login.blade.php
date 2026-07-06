<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentSys ERP — Connexion</title>
    <script src="/js/tailwind.cdn.js"></script>
    <link href="/fonts/remixicon/remixicon.css" rel="stylesheet">
    <script defer src="/js/alpine.min.js"></script>
    <style>
        .login-bg { position: relative; overflow: hidden; }
        .login-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url('/images/login_bg.jpg');
            background-size: cover;
            background-position: center;
            transform: scaleX(-1);
            z-index: 0;
        }
    </style>
</head>
<body class="min-h-screen" x-data="{ showPwd: false, loading: false }">

{{-- ── Plein écran : image + overlay ───────────────────────────────────── --}}
<div class="login-bg min-h-screen relative flex flex-col">

    {{-- Overlay violet/indigo --}}
    <div class="absolute inset-0 bg-indigo-900/75" style="z-index:1"></div>

    {{-- Contenu principal --}}
    <div class="relative flex flex-col min-h-screen" style="z-index:2">

        {{-- ── Logo (haut gauche) ─────────────────────────────── --}}
        <div class="flex items-center gap-3 px-8 pt-7">
            <div class="w-11 h-11 rounded-xl overflow-hidden border border-white/20 shadow-lg">
                <img src="/images/logo_talentsys.jpeg" alt="TalentSys" class="w-full h-full object-cover">
            </div>
            <div>
                <p class="text-white font-bold text-lg leading-tight">TalentSys</p>
                <p class="text-white/55 text-xs">Gestion Scolaire</p>
            </div>
        </div>

        {{-- ── Zone principale : slogan (gauche) + carte (droite) ─ --}}
        <div class="flex flex-1 items-center justify-between px-16 py-8 gap-8">

            {{-- Slogan gauche --}}
            <div class="hidden lg:flex flex-col max-w-xl pl-4">
                <h1 class="text-5xl font-bold text-white leading-tight mb-4">
                    Gérez votre<br>établissement<br>avec excellence
                </h1>
                <p class="text-white/60 text-base mb-10">
                    Étudiants, enseignants, finances, évaluations —<br>
                    tout en un seul tableau de bord unifié.
                </p>
                <div class="flex flex-wrap gap-2.5">
                    @foreach([
                        ['ri-graduation-cap-line', 'Étudiants'],
                        ['ri-calendar-2-line',     'Emploi du temps'],
                        ['ri-file-list-3-line',    'Notes & Bulletins'],
                        ['ri-bank-card-line',      'Finances'],
                    ] as $b)
                    <span class="inline-flex items-center gap-2 text-sm font-medium text-white/80
                                 bg-white/10 border border-white/20 rounded-full px-4 py-2 backdrop-blur">
                        <i class="{{ $b[0] }} text-base"></i>
                        {{ $b[1] }}
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- Carte flottante droite --}}
            <div class="w-full lg:w-auto flex-shrink-0 mr-8">
                <div class="bg-white rounded-3xl shadow-2xl px-10 py-12 w-full lg:w-[420px]">

                    {{-- Icône verrou --}}
                    <div class="flex justify-center mb-7">
                        <div class="w-20 h-20 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-300">
                            <i class="ri-lock-2-fill text-white text-3xl"></i>
                        </div>
                    </div>

                    <h2 class="text-3xl font-bold text-gray-900 text-center mb-1.5">Connexion</h2>
                    <p class="text-gray-400 text-sm text-center mb-9">Accédez à votre espace de gestion</p>

                    {{-- Erreur auth --}}
                    @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 flex items-start gap-2 text-sm">
                        <i class="ri-error-warning-line text-lg flex-shrink-0 mt-0.5"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login.submit') }}" @submit="loading = true">
                        @csrf

                        {{-- Email --}}
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Adresse Email</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="ri-mail-line text-lg"></i>
                                </span>
                                <input type="email" name="mail" value="{{ old('mail') }}"
                                       placeholder="admin@uta.cm"
                                       required autocomplete="email"
                                       class="w-full pl-11 pr-4 py-4 border border-gray-200 rounded-xl text-sm bg-gray-50
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white
                                              transition-all {{ $errors->has('mail') ? 'border-red-300 bg-red-50' : '' }}">
                            </div>
                        </div>

                        {{-- Mot de passe --}}
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Mot de passe</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="ri-lock-line text-lg"></i>
                                </span>
                                <input :type="showPwd ? 'text' : 'password'"
                                       name="password"
                                       placeholder="••••••••"
                                       required autocomplete="current-password"
                                       class="w-full pl-11 pr-12 py-4 border border-gray-200 rounded-xl text-sm bg-gray-50
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white
                                              transition-all">
                                <button type="button" @click="showPwd = !showPwd"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <i :class="showPwd ? 'ri-eye-off-line' : 'ri-eye-line'" class="text-lg"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Se souvenir / Mot de passe oublié --}}
                        <div class="flex items-center justify-between mb-8">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember" id="remember"
                                       class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-600">Se souvenir de moi</span>
                            </label>
                            <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                                Mot de passe oublié ?
                            </a>
                        </div>

                        {{-- Bouton connexion --}}
                        <button type="submit"
                                :disabled="loading"
                                class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl
                                       transition-all duration-150 flex items-center justify-center gap-2
                                       shadow-lg shadow-indigo-200 hover:shadow-indigo-300
                                       disabled:opacity-70 disabled:cursor-not-allowed">
                            <template x-if="loading">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </template>
                            <template x-if="!loading">
                                <i class="ri-login-circle-line text-lg"></i>
                            </template>
                            <span x-text="loading ? 'Connexion en cours...' : 'Se connecter'"></span>
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- ── Footer ──────────────────────────────────────────── --}}
        <div class="relative z-10 px-8 pb-6">
            <p class="text-white/30 text-xs">
                &copy; {{ date('Y') }}-{{ date('Y')+1 }} TalentSys
                @if(session('etablissement.nom'))
                    &bull; {{ session('etablissement.nom') }}
                @endif
            </p>
        </div>

    </div>
</div>

</body>
</html>
