<x-app-layout title="Tableau de Bord — TalentSys">

@push('styles')
<style>
    .portal-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 16px 10px 14px;
        background: #ffffff;
        border-radius: 14px;
        text-align: center;
        transition: all 0.15s ease;
        text-decoration: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .portal-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }
    .portal-card .icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }
    .portal-card span {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        line-height: 1.3;
        color: #374151;
    }
</style>
@endpush

<div class="space-y-5">

    {{-- En-tête --}}
    <div>
        <h1 class="text-xl font-bold text-gray-800">Tableau de Bord</h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ $anneeActive?->libelle ?? date('Y').'-'.(date('Y')+1) }}</p>
    </div>

    {{-- ── PORTAIL SCOLAIRE ──────────────────────────────────────────── --}}
    <div class="rounded-2xl p-5 md:p-6" style="background-color: var(--primary, #5A67D8)">
        <p class="text-center text-white/50 font-semibold text-[11px] tracking-widest mb-5 uppercase">
            Portail Scolaire
        </p>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7 gap-2.5">
            @php
            $portail = [
                ['icon' => 'ri-layout-grid-fill',    'label' => 'Filières',         'bg' => '#EEF2FF', 'color' => '#4F46E5'],
                ['icon' => 'ri-table-2',             'label' => 'Classes',          'bg' => '#FFF1F2', 'color' => '#BE123C'],
                ['icon' => 'ri-book-2-fill',         'label' => 'Matières',         'bg' => '#FFF7ED', 'color' => '#EA580C'],
                ['icon' => 'ri-checkbox-circle-fill','label' => 'Inscriptions',     'bg' => '#F0FDF4', 'color' => '#16A34A'],
                ['icon' => 'ri-team-fill',           'label' => 'Enseignants',      'bg' => '#EFF6FF', 'color' => '#2563EB'],
                ['icon' => 'ri-briefcase-4-fill',    'label' => 'Affectations',     'bg' => '#F0F9FF', 'color' => '#0284C7'],
                ['icon' => 'ri-time-fill',           'label' => 'Pointage Ens.',    'bg' => '#F0FDF4', 'color' => '#15803D'],
                ['icon' => 'ri-calendar-check-fill', 'label' => 'Emploi du Temps',  'bg' => '#FAF5FF', 'color' => '#7C3AED'],
                ['icon' => 'ri-edit-box-fill',       'label' => 'Notes',            'bg' => '#FFFBEB', 'color' => '#D97706'],
                ['icon' => 'ri-award-fill',          'label' => 'Bulletins',        'bg' => '#FFF7ED', 'color' => '#C2410C'],
                ['icon' => 'ri-bank-card-fill',      'label' => 'Paiements',        'bg' => '#F0FDF4', 'color' => '#059669'],
                ['icon' => 'ri-pie-chart-2-fill',    'label' => 'Trésorerie',       'bg' => '#F5F3FF', 'color' => '#7C3AED'],
                ['icon' => 'ri-store-3-fill',        'label' => 'Gestion de Stock', 'bg' => '#F8FAFC', 'color' => '#475569'],
            ];
            @endphp
            @foreach($portail as $item)
            <a href="#" class="portal-card">
                <div class="icon-wrap" style="background:{{ $item['bg'] }}">
                    <i class="{{ $item['icon'] }}" style="color:{{ $item['color'] }}"></i>
                </div>
                <span>{{ $item['label'] }}</span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ── STATISTIQUES ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-purple-100 flex items-center justify-center">
                    <i class="ri-graduation-cap-fill text-purple-600 text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-gray-300 uppercase tracking-wider">Étudiants</span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['etudiants']) }}</p>
            <p class="text-xs text-gray-400 mt-1">inscrits cette année</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-teal-100 flex items-center justify-center">
                    <i class="ri-user-star-fill text-teal-600 text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-gray-300 uppercase tracking-wider">Enseignants</span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['enseignants']) }}</p>
            <p class="text-xs text-gray-400 mt-1">actifs</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-orange-100 flex items-center justify-center">
                    <i class="ri-money-dollar-circle-fill text-orange-500 text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-gray-300 uppercase tracking-wider">Chiffres d'Affaire</span>
            </div>
            @php
                $ca = $stats['chiffre_affaire'] ?? 0;
                if ($ca >= 1000000)  $caFmt = number_format($ca/1000000, 1).'M FCFA';
                elseif ($ca >= 1000) $caFmt = number_format($ca/1000, 0).'K FCFA';
                else                 $caFmt = number_format($ca, 0).' FCFA';
            @endphp
            <p class="text-2xl font-bold text-gray-900">{{ $caFmt }}</p>
            <p class="text-xs text-gray-400 mt-1">recouvrées</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i class="ri-line-chart-fill text-blue-600 text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-gray-300 uppercase tracking-wider">Inscriptions</span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['inscriptions']) }}</p>
            <p class="text-xs text-gray-400 mt-1">cette année</p>
        </div>
    </div>

    {{-- ── GRAPHIQUES ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <div class="lg:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <p class="font-semibold text-gray-800">Étudiants par Niveau</p>
                <span class="text-[11px] text-gray-400 bg-gray-50 border border-gray-100 px-2.5 py-1 rounded-full">
                    Année en cours
                </span>
            </div>
            <canvas id="chartNiveaux" height="110"></canvas>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <p class="font-semibold text-gray-800">Répartition Filières</p>
            </div>
            <canvas id="chartFilieres" height="110"></canvas>
        </div>
    </div>

    {{-- ── ANALYSE FINANCIÈRE (accordéon) ───────────────────────────── --}}
    <div x-data="{ open: false }" class="rounded-2xl overflow-hidden shadow-sm">
        <button @click="open = !open"
                class="w-full flex items-center justify-between px-5 py-4 font-semibold text-white text-sm transition-colors"
                style="background-color: var(--primary, #5A67D8)">
            <span class="flex items-center gap-2.5">
                <i class="ri-bar-chart-box-fill text-lg"></i>
                Analyse Financière &amp; Prévisionnelle
            </span>
            <i class="ri-arrow-down-s-line text-xl transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
        </button>
        <div x-show="open" x-collapse class="bg-slate-50 border border-gray-100 border-t-0 rounded-b-2xl">
            <div class="p-5 space-y-4">

                {{-- ── Trois cartes situation ──────────────────────────── --}}
                @php
                    function fmtFcfa($v) {
                        if ($v >= 1000000) return number_format($v/1000000,1).' 000 FCFA'[0] === '0'
                            ? number_format($v/1000000,3,' ',' ').' FCFA'
                            : number_format($v,0,' ',' ').' FCFA';
                        return number_format($v,0,' ',' ').' FCFA';
                    }
                    function fFcfa($v) {
                        return number_format($v, 0, ',', ' ').' FCFA';
                    }
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    {{-- Situation Actuelle --}}
                    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="ri-bar-chart-grouped-fill text-blue-600"></i>
                            </div>
                            <p class="font-semibold text-blue-700 text-sm">Situation Actuelle</p>
                        </div>
                        <div class="h-px bg-gray-100 mb-4"></div>
                        <div class="space-y-2.5 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Effectifs actuels</span>
                                <span class="font-semibold text-gray-800">{{ number_format($situationActuelle['effectifs']) }} eleves</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Revenus actuels</span>
                                <span class="font-semibold text-gray-800">{{ fFcfa($situationActuelle['revenus']) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Taux de remplissage</span>
                                <span class="font-semibold text-gray-800">{{ $situationActuelle['taux'] }}%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Situation Optimale --}}
                    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="ri-checkbox-circle-fill text-green-600"></i>
                            </div>
                            <p class="font-semibold text-green-700 text-sm">Situation Optimale</p>
                        </div>
                        <div class="h-px bg-gray-100 mb-4"></div>
                        <div class="space-y-2.5 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Effectifs optimaux</span>
                                <span class="font-semibold text-gray-800">{{ number_format($situationOptimale['effectifs']) }} eleves</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Revenus maximaux</span>
                                <span class="font-semibold text-gray-800">{{ fFcfa($situationOptimale['revenus']) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Taux de remplissage</span>
                                <span class="font-semibold text-gray-800">{{ $situationOptimale['taux'] }}%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Écarts --}}
                    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="ri-arrow-left-right-fill text-red-500"></i>
                            </div>
                            <p class="font-semibold text-red-600 text-sm">Ecarts</p>
                        </div>
                        <div class="h-px bg-gray-100 mb-4"></div>
                        <div class="space-y-2.5 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Places vacantes</span>
                                <span class="font-semibold text-gray-800">{{ number_format($ecarts['places_vacantes']) }} places</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Perte financiere</span>
                                <span class="font-semibold text-red-500">{{ fFcfa($ecarts['perte']) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Classes sous-utilisees (&lt;50%)</span>
                                <span class="font-semibold text-orange-500">{{ $ecarts['classes_sous_utilisees'] }} classe(s)</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Synthèses par Niveau + par Filière ──────────────── --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                    {{-- Synthese par Niveau --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50">
                            <div class="flex items-center gap-2">
                                <i class="ri-graduation-cap-line text-blue-500 text-lg"></i>
                                <p class="font-semibold text-gray-800 text-sm">Synthese par Niveau</p>
                            </div>
                            <span class="text-xs text-gray-400 bg-gray-50 border border-gray-100 px-2 py-1 rounded-full">
                                {{ $analyseNiveaux->count() }} total
                            </span>
                        </div>
                        <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                            @forelse($analyseNiveaux as $niv)
                            @php
                                $tauxColor = $niv->taux >= 75 ? 'bg-green-500' : ($niv->taux >= 50 ? 'bg-orange-400' : 'bg-red-500');
                                $tauxText  = $niv->taux >= 75 ? 'text-green-600' : ($niv->taux >= 50 ? 'text-orange-500' : 'text-red-500');
                                $tauxBg    = $niv->taux >= 75 ? 'bg-green-50 text-green-600' : ($niv->taux >= 50 ? 'bg-orange-50 text-orange-600' : 'bg-red-50 text-red-600');
                            @endphp
                            <div class="px-5 py-3.5">
                                <div class="flex items-center justify-between mb-1.5">
                                    <p class="font-semibold text-gray-800 text-sm">{{ $niv->libelle }}</p>
                                    <span class="text-xs font-bold {{ $tauxBg }} px-2 py-0.5 rounded-full">{{ $niv->taux }}%</span>
                                </div>
                                {{-- Barre de progression --}}
                                <div class="w-full h-1 bg-gray-100 rounded-full mb-3 overflow-hidden">
                                    <div class="h-full {{ $tauxColor }} rounded-full transition-all"
                                         style="width: {{ min(100, $niv->taux) }}%"></div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <p class="text-gray-400 mb-0.5">Eleves</p>
                                        <p class="font-semibold text-gray-700">{{ $niv->inscrits }} / {{ $niv->capacite }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 mb-0.5">Recette</p>
                                        <p class="font-semibold text-gray-700">{{ fFcfa($niv->recette) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 mb-0.5">Manque</p>
                                        <p class="font-semibold text-red-500">{{ fFcfa($niv->manque) }}</p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="px-5 py-8 text-center text-gray-400 text-sm">
                                <i class="ri-inbox-line text-3xl block mb-2 text-gray-200"></i>
                                Aucune donnée disponible
                            </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Synthese par Filière --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50">
                            <div class="flex items-center gap-2">
                                <i class="ri-share-circle-line text-indigo-500 text-lg"></i>
                                <p class="font-semibold text-gray-800 text-sm">Synthese par Filiere</p>
                            </div>
                            <span class="text-xs text-gray-400 bg-gray-50 border border-gray-100 px-2 py-1 rounded-full">
                                {{ $analyseFilieres->count() }} total
                            </span>
                        </div>
                        <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                            @forelse($analyseFilieres as $fil)
                            @php
                                $tauxColor = $fil->taux >= 75 ? 'bg-green-500' : ($fil->taux >= 50 ? 'bg-orange-400' : 'bg-red-500');
                                $tauxBg    = $fil->taux >= 75 ? 'bg-green-50 text-green-600' : ($fil->taux >= 50 ? 'bg-orange-50 text-orange-600' : 'bg-red-50 text-red-600');
                            @endphp
                            <div class="px-5 py-3.5">
                                <div class="flex items-center justify-between mb-1.5">
                                    <p class="font-semibold text-gray-800 text-sm">{{ $fil->libelle }}</p>
                                    <span class="text-xs font-bold {{ $tauxBg }} px-2 py-0.5 rounded-full">{{ $fil->taux }}%</span>
                                </div>
                                <div class="w-full h-1 bg-gray-100 rounded-full mb-3 overflow-hidden">
                                    <div class="h-full {{ $tauxColor }} rounded-full"
                                         style="width: {{ min(100, $fil->taux) }}%"></div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <p class="text-gray-400 mb-0.5">Eleves</p>
                                        <p class="font-semibold text-gray-700">{{ $fil->inscrits }} / {{ $fil->capacite }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 mb-0.5">Recette</p>
                                        <p class="font-semibold text-gray-700">{{ fFcfa($fil->recette) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 mb-0.5">Manque</p>
                                        <p class="font-semibold text-red-500">{{ fFcfa($fil->manque) }}</p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="px-5 py-8 text-center text-gray-400 text-sm">
                                <i class="ri-inbox-line text-3xl block mb-2 text-gray-200"></i>
                                Aucune donnée disponible
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── ACTIVITÉ RÉCENTE + STATISTIQUES RAPIDES ──────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Activité Récente --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h3 class="font-semibold text-gray-800 mb-4">Activité Récente</h3>

            @if($activiteRecente->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">Aucune activité récente.</p>
            @else
            <div class="space-y-4">
                @foreach($activiteRecente as $item)
                <div class="flex items-start gap-3">
                    {{-- Dot couleur selon type --}}
                    <div class="flex-shrink-0 mt-1.5">
                        @if($item->type === 'paiement')
                            <span class="block w-2.5 h-2.5 rounded-full bg-green-500"></span>
                        @else
                            <span class="block w-2.5 h-2.5 rounded-full bg-blue-400"></span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 font-medium">
                            @if($item->type === 'paiement')
                                Paiement de
                                @php
                                    $m = (float)($item->montant ?? 0);
                                    echo $m >= 1000000 ? number_format($m/1000000,1).'M FCFA'
                                       : ($m >= 1000 ? number_format($m/1000,0).'K FCFA'
                                       : number_format($m,0).' FCFA');
                                @endphp
                                – {{ trim($item->nom) }}
                            @else
                                Inscription de {{ trim($item->nom) }}
                            @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $item->date }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Statistiques Rapides --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h3 class="font-semibold text-gray-800 mb-4">Statistiques Rapides</h3>
            <div class="space-y-1">

                @php
                $rapides = [
                    ['icon' => 'ri-table-2',              'color' => 'text-red-500',    'label' => 'Classes',              'val' => $statsRapides['classes'],  'fmt' => 'int'],
                    ['icon' => 'ri-layout-grid-fill',     'color' => 'text-indigo-500', 'label' => 'Filières',             'val' => $statsRapides['filieres'], 'fmt' => 'int'],
                    ['icon' => 'ri-user-add-fill',        'color' => 'text-teal-500',   'label' => 'Inscriptions en cours', 'val' => $stats['inscriptions'],   'fmt' => 'int'],
                    ['icon' => 'ri-book-2-fill',          'color' => 'text-orange-500', 'label' => 'Matières',             'val' => $statsRapides['matieres'], 'fmt' => 'int'],
                    ['icon' => 'ri-money-dollar-circle-fill','color' => 'text-green-600','label' => "Chiffres d'affaire",  'val' => $stats['chiffre_affaire'], 'fmt' => 'fcfa', 'highlight' => 'green'],
                    ['icon' => 'ri-wallet-3-fill',        'color' => 'text-violet-500', 'label' => 'Masse salariale',      'val' => $statsRapides['masse_salariale'], 'fmt' => 'fcfa', 'highlight' => 'violet'],
                ];
                @endphp

                @foreach($rapides as $r)
                <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                    <div class="flex items-center gap-3">
                        <i class="{{ $r['icon'] }} {{ $r['color'] }} text-lg w-5 text-center"></i>
                        <span class="text-sm text-gray-600">{{ $r['label'] }}</span>
                    </div>
                    @php
                        $v = $r['val'] ?? 0;
                        if ($r['fmt'] === 'fcfa') {
                            $vFmt = $v >= 1000000
                                ? number_format($v/1000000,1).'M FCFA'
                                : ($v >= 1000 ? number_format($v/1000,0).'K FCFA' : number_format($v,0).' FCFA');
                        } else {
                            $vFmt = number_format($v);
                        }
                        $hl = $r['highlight'] ?? null;
                    @endphp
                    <span class="text-sm font-bold
                        {{ $hl === 'green'  ? 'text-green-600' : '' }}
                        {{ $hl === 'violet' ? 'text-violet-600' : '' }}
                        {{ !$hl ? 'text-gray-800' : '' }}">
                        {{ $vFmt }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#5A67D8';

    // Bar chart — Étudiants par Niveau
    const niveauxLabels = @json($chartNiveaux->pluck('libelle'));
    const niveauxData   = @json($chartNiveaux->pluck('total')->map(fn($v) => (int)$v));

    new Chart(document.getElementById('chartNiveaux'), {
        type: 'bar',
        data: {
            labels: niveauxLabels.length ? niveauxLabels : ['L1','L2','L3','M1','M2','D1','D2'],
            datasets: [{
                data: niveauxData.length ? niveauxData : [0,0,0,0,0,0,0],
                backgroundColor: primary,
                borderRadius: 4,
                borderSkipped: false,
                maxBarThickness: 28,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed.y + ' étudiant(s)' }}
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 }, color: '#94a3b8' },
                    grid: { color: '#f1f5f9' },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#94a3b8' },
                    border: { display: false }
                }
            }
        }
    });

    // Donut chart — Répartition Filières
    const filieresLabels = @json($chartFilieres->pluck('libelle'));
    const filieresData   = @json($chartFilieres->pluck('total')->map(fn($v) => (int)$v));
    const palette = ['#6366f1','#8b5cf6','#a78bfa','#c084fc','#f472b6','#34d399','#fb923c','#60a5fa','#facc15','#4ade80'];

    new Chart(document.getElementById('chartFilieres'), {
        type: 'doughnut',
        data: {
            labels: filieresLabels.length ? filieresLabels : ['Aucune donnée'],
            datasets: [{
                data: filieresData.length ? filieresData : [1],
                backgroundColor: filieresLabels.length ? palette.slice(0, filieresLabels.length) : ['#e5e7eb'],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            cutout: '72%',
            radius: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11 }, padding: 14, color: '#6b7280', boxWidth: 12, boxHeight: 12 }
                }
            }
        }
    });
})();
</script>
@endpush

</x-app-layout>
