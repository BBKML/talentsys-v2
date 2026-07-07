@php
    $user = Auth::user();
    $role = $user?->role;
    $isSuper = $role?->is_super_admin || ($user?->id_role == 8);
    $lib = strtolower($role?->libelle ?? '');

    $canUtilisateurs  = $isSuper || ($role?->voir_utilisateurs  ?? false);
    $canEtablissement = $isSuper || ($role?->voir_etablissement ?? false);
    $canAcademique    = $isSuper || ($role?->voir_academique    ?? (str_contains($lib,'admin')||str_contains($lib,'direct')||str_contains($lib,'secr')));
    $canEnseignants   = $isSuper || ($role?->voir_enseignants   ?? (str_contains($lib,'admin')||str_contains($lib,'direct')||str_contains($lib,'secr')));
    $canEtudiants     = $isSuper || ($role?->voir_etudiants     ?? (str_contains($lib,'admin')||str_contains($lib,'direct')||str_contains($lib,'secr')));
    $canFinance       = $isSuper || ($role?->voir_finance       ?? (str_contains($lib,'admin')||str_contains($lib,'direct')||str_contains($lib,'caissier')||str_contains($lib,'compt')));
    $canEvaluations   = $isSuper || ($role?->voir_evaluations   ?? (str_contains($lib,'admin')||str_contains($lib,'direct')||str_contains($lib,'secr')));
    $canComptabilite  = $isSuper || ($role?->voir_comptabilite  ?? (str_contains($lib,'admin')||str_contains($lib,'direct')||str_contains($lib,'compt')));
    $canAchats        = $isSuper || ($role?->voir_achats        ?? (str_contains($lib,'admin')||str_contains($lib,'direct')||str_contains($lib,'achat')));
    $canGed           = $isSuper || ($role?->voir_ged           ?? (str_contains($lib,'admin')||str_contains($lib,'direct')||str_contains($lib,'secr')));

    $route = request()->route()?->getName() ?? '';
    $isUtilisateurs   = str_starts_with($route, 'utilisateurs') || str_starts_with($route, 'roles') || str_starts_with($route, 'direction') || $route === 'historique.index';
    $isEtablissement  = str_starts_with($route, 'etablissement.') || str_starts_with($route, 'salles.');
    $isAcademique     = in_array($route, ['annees.index','decoupage.index','filieres.index','niveaux.index','classes.index','classes.etudiants','bourses.index','frais.index','modalites.index','ue.index','matieres.index']);
    $isEnseignants    = in_array($route, ['enseignants.index','affectations.index','emploi.index','volume.index','salaires.index']);
    $isEvaluations    = in_array($route, ['notes.index','moyennes.index','avance.index','deliberations.index']);
    $isTresorerie     = str_starts_with($route, 'tresorerie.');
    $isAchats         = str_starts_with($route, 'achats.');
    $isEtudiants      = str_starts_with($route, 'etudiants.');
@endphp

{{-- ── Tableau de Bord ──────────────────────────────────── --}}
<a href="{{ route('dashboard') }}"
   class="sidebar-item {{ $route === 'dashboard' ? 'active' : '' }}">
    <i class="ri-dashboard-2-fill text-lg"></i>
    <span>Tableau de Bord</span>
</a>

{{-- ── Utilisateurs ──────────────────────────────────────── --}}
@if($canUtilisateurs)
<div x-data="{ open: {{ $isUtilisateurs ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-item w-full {{ $isUtilisateurs ? 'active' : '' }}">
        <i class="ri-shield-user-fill text-lg"></i>
        <span class="flex-1 text-left">Utilisateurs</span>
        <i class="ri-arrow-right-s-line text-base transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
    </button>
    <div x-show="open" x-collapse>
        <a href="{{ route('utilisateurs.index') }}"
           class="sub-item {{ $route === 'utilisateurs.index' ? 'active' : '' }}">
            <i class="ri-group-fill text-sm"></i> Utilisateurs
        </a>
        <a href="{{ route('roles.index') }}"
           class="sub-item {{ $route === 'roles.index' ? 'active' : '' }}">
            <i class="ri-shield-fill text-sm"></i> Rôles &amp; Permissions
        </a>
        <a href="{{ route('direction.index') }}"
           class="sub-item {{ $route === 'direction.index' ? 'active' : '' }}">
            <i class="ri-user-fill text-sm"></i> Direction
        </a>
        <a href="{{ route('historique.index') }}"
           class="sub-item {{ $route === 'historique.index' ? 'active' : '' }}">
            <i class="ri-history-fill text-sm"></i> Historique
        </a>
    </div>
</div>
@endif

{{-- ── Établissement ─────────────────────────────────────── --}}
@if($canEtablissement)
<div x-data="{ open: {{ $isEtablissement ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-item w-full {{ $isEtablissement ? 'active' : '' }}">
        <i class="ri-building-2-fill text-lg"></i>
        <span class="flex-1 text-left">Établissement</span>
        <i class="ri-arrow-right-s-line text-base transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
    </button>
    <div x-show="open" x-collapse>
        <a href="{{ route('etablissement.informations') }}"
           class="sub-item {{ $route === 'etablissement.informations' ? 'active' : '' }}">
            <i class="ri-information-2-fill text-sm"></i> Informations
        </a>
        <a href="{{ route('etablissement.couleurs') }}"
           class="sub-item {{ $route === 'etablissement.couleurs' ? 'active' : '' }}">
            <i class="ri-palette-fill text-sm"></i> Couleurs
        </a>
        <a href="{{ route('salles.index') }}"
           class="sub-item {{ $route === 'salles.index' ? 'active' : '' }}">
            <i class="ri-door-open-fill text-sm"></i> Salles
        </a>
    </div>
</div>
@endif

{{-- ── Académique ────────────────────────────────────────── --}}
@if($canAcademique)
<div x-data="{ open: {{ $isAcademique ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-item w-full {{ $isAcademique ? 'active' : '' }}">
        <i class="ri-book-open-fill text-lg"></i>
        <span class="flex-1 text-left">Académique</span>
        <i class="ri-arrow-right-s-line text-base transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
    </button>
    <div x-show="open">
        <a href="{{ route('annees.index') }}" class="sub-item {{ $route === 'annees.index' ? 'active' : '' }}">
            <i class="ri-calendar-check-line text-sm"></i> Années Scolaires
        </a>
        <a href="{{ route('decoupage.index') }}" class="sub-item {{ $route === 'decoupage.index' ? 'active' : '' }}">
            <i class="ri-layout-grid-line text-sm"></i> Découpage Année
        </a>
        <a href="{{ route('filieres.index') }}" class="sub-item {{ $route === 'filieres.index' ? 'active' : '' }}">
            <i class="ri-bookmark-3-line text-sm"></i> Filières
        </a>
        <a href="{{ route('niveaux.index') }}" class="sub-item {{ $route === 'niveaux.index' ? 'active' : '' }}">
            <i class="ri-stack-line text-sm"></i> Niveaux
        </a>
        <a href="{{ route('classes.index') }}" class="sub-item {{ $route === 'classes.index' ? 'active' : '' }}">
            <i class="ri-group-line text-sm"></i> Classes
        </a>
        <a href="{{ route('bourses.index') }}" class="sub-item {{ $route === 'bourses.index' ? 'active' : '' }}"><i class="ri-medal-line text-sm"></i> Bourses</a>
        <a href="{{ route('frais.index') }}" class="sub-item {{ $route === 'frais.index' ? 'active' : '' }}"><i class="ri-money-dollar-circle-line text-sm"></i> Frais de Scolarité</a>
        <a href="{{ route('modalites.index') }}" class="sub-item {{ $route === 'modalites.index' ? 'active' : '' }}"><i class="ri-wallet-3-line text-sm"></i> Modalités Paiement</a>
        <a href="{{ route('ue.index') }}" class="sub-item {{ $route === 'ue.index' ? 'active' : '' }}">
            <i class="ri-book-2-line text-sm"></i> Unités d'Enseignement
        </a>
        <a href="{{ route('matieres.index') }}" class="sub-item {{ $route === 'matieres.index' ? 'active' : '' }}">
            <i class="ri-file-text-line text-sm"></i> Matières
        </a>
    </div>
</div>
@endif

{{-- ── Enseignants ───────────────────────────────────────── --}}
@if($canEnseignants)
<div x-data="{ open: {{ $isEnseignants ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-item w-full {{ $isEnseignants ? 'active' : '' }}">
        <i class="ri-user-star-fill text-lg"></i>
        <span class="flex-1 text-left">Enseignants</span>
        <i class="ri-arrow-right-s-line text-base transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
    </button>
    <div x-show="open" x-collapse>
        <a href="{{ route('enseignants.index') }}" class="sub-item {{ $route === 'enseignants.index' ? 'active' : '' }}">
            <i class="ri-user-line text-sm"></i> Liste Enseignants
        </a>
        <a href="{{ route('affectations.index') }}" class="sub-item {{ $route === 'affectations.index' ? 'active' : '' }}">
            <i class="ri-user-settings-line text-sm"></i> Affectation Enseignant
        </a>
        <a href="{{ route('emploi.index') }}" class="sub-item {{ $route === 'emploi.index' ? 'active' : '' }}">
            <i class="ri-calendar-2-line text-sm"></i> Emploi du Temps
        </a>
        <a href="{{ route('volume.index') }}" class="sub-item {{ $route === 'volume.index' ? 'active' : '' }}">
            <i class="ri-time-line text-sm"></i> Volume Horaire
        </a>
        <a href="{{ route('salaires.index') }}" class="sub-item {{ $route === 'salaires.index' ? 'active' : '' }}">
            <i class="ri-money-cny-circle-line text-sm"></i> Salaires
        </a>
    </div>
</div>
@endif

{{-- ── Étudiants ─────────────────────────────────────────── --}}
@if($canEtudiants)
<div x-data="{ open: {{ $isEtudiants ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-item w-full {{ $isEtudiants ? 'active' : '' }}">
        <i class="ri-graduation-cap-fill text-lg"></i>
        <span class="flex-1 text-left">Étudiants</span>
        <i class="ri-arrow-right-s-line text-base transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
    </button>
    <div x-show="open" x-collapse>
        <a href="{{ route('etudiants.parents.index') }}" class="sub-item {{ $route === 'etudiants.parents.index' ? 'active' : '' }}"><i class="ri-team-line text-sm"></i> Parents</a>
        <a href="{{ route('etudiants.index') }}" class="sub-item {{ $route === 'etudiants.index' ? 'active' : '' }}"><i class="ri-graduation-cap-line text-sm"></i> Liste Étudiants</a>
        <a href="{{ route('etudiants.inscriptions.index') }}" class="sub-item {{ $route === 'etudiants.inscriptions.index' ? 'active' : '' }}"><i class="ri-user-add-line text-sm"></i> Inscriptions</a>
        <a href="{{ route('etudiants.dossiers.index') }}" class="sub-item {{ $route === 'etudiants.dossiers.index' ? 'active' : '' }}"><i class="ri-folder-open-line text-sm"></i> Dossiers</a>
        <a href="{{ route('etudiants.boursiers.index') }}" class="sub-item {{ $route === 'etudiants.boursiers.index' ? 'active' : '' }}"><i class="ri-trophy-line text-sm"></i> Liste Boursiers</a>
        <a href="{{ route('etudiants.credits.index') }}" class="sub-item {{ $route === 'etudiants.credits.index' ? 'active' : '' }}"><i class="ri-coin-line text-sm"></i> Crédits</a>
        <a href="{{ route('etudiants.parcours.index') }}" class="sub-item {{ $route === 'etudiants.parcours.index' ? 'active' : '' }}"><i class="ri-history-line text-sm"></i> Parcours Scolaire</a>
    </div>
</div>
@endif

{{-- ── Évaluations ───────────────────────────────────────── --}}
@if($canEvaluations)
<div x-data="{ open: {{ $isEvaluations ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-item w-full {{ $isEvaluations ? 'active' : '' }}">
        <i class="ri-file-list-3-fill text-lg"></i>
        <span class="flex-1 text-left">Évaluations</span>
        <i class="ri-arrow-right-s-line text-base transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
    </button>
    <div x-show="open" x-collapse>
        <a href="{{ route('notes.index') }}" class="sub-item {{ $route === 'notes.index' ? 'active' : '' }}">
            <i class="ri-edit-2-line text-sm"></i> Notes
        </a>
        <a href="{{ route('moyennes.index') }}" class="sub-item {{ $route === 'moyennes.index' ? 'active' : '' }}">
            <i class="ri-function-line text-sm"></i> Moyennes
        </a>
        <a href="{{ route('avance.index') }}" class="sub-item {{ $route === 'avance.index' ? 'active' : '' }}">
            <i class="ri-bar-chart-box-line text-sm"></i> Avancé
        </a>
        <a href="{{ route('deliberations.index') }}" class="sub-item {{ $route === 'deliberations.index' ? 'active' : '' }}">
            <i class="ri-scales-3-line text-sm"></i> Délibérations
        </a>
    </div>
</div>
@endif

{{-- ── Finance ───────────────────────────────────────────── --}}
@if($canFinance)
<div x-data="{ open: false }">
    <button @click="open = !open" class="sidebar-item w-full">
        <i class="ri-money-dollar-circle-fill text-lg"></i>
        <span class="flex-1 text-left">Finance</span>
        <i class="ri-arrow-right-s-line text-base transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
    </button>
    <div x-show="open" x-collapse>
        <a href="{{ route('finance.echeanciers') }}" class="sub-item {{ request()->routeIs('finance.echeanciers') ? 'active' : '' }}"><i class="ri-calendar-todo-line text-sm"></i> Échéanciers</a>
        <a href="{{ route('finance.tranches_prevues') }}" class="sub-item {{ request()->routeIs('finance.tranches_prevues') ? 'active' : '' }}"><i class="ri-list-check-2 text-sm"></i> Tranches Prévues</a>
        <a href="{{ route('finance.paiements') }}" class="sub-item {{ request()->routeIs('finance.paiements') ? 'active' : '' }}"><i class="ri-receipt-line text-sm"></i> Paiements</a>
        <a href="{{ route('finance.factures') }}" class="sub-item {{ request()->routeIs('finance.factures') ? 'active' : '' }}"><i class="ri-file-list-3-line text-sm"></i> Factures</a>
    </div>
</div>
@endif

{{-- ── Trésorerie ────────────────────────────────────────── --}}
@if($canComptabilite)
<div x-data="{ open: {{ $isTresorerie ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-item w-full {{ $isTresorerie ? 'active' : '' }}">
        <i class="ri-bank-fill text-lg"></i>
        <span class="flex-1 text-left">Trésorerie</span>
        <i class="ri-arrow-right-s-line text-base transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
    </button>
    <div x-show="open" x-collapse>
        <a href="{{ route('tresorerie.operations') }}" class="sub-item {{ $route === 'tresorerie.operations' ? 'active' : '' }}"><i class="ri-list-unordered text-sm"></i> Opérations</a>
        <a href="{{ route('tresorerie.categoriesBilan') }}" class="sub-item {{ $route === 'tresorerie.categoriesBilan' ? 'active' : '' }}"><i class="ri-pie-chart-line text-sm"></i> Catégories &amp; Bilan</a>
        <a href="{{ route('tresorerie.planComptable') }}" class="sub-item {{ $route === 'tresorerie.planComptable' ? 'active' : '' }}"><i class="ri-book-read-line text-sm"></i> Plan Comptable</a>
        <a href="{{ route('tresorerie.parametrage') }}" class="sub-item {{ $route === 'tresorerie.parametrage' ? 'active' : '' }}"><i class="ri-settings-4-line text-sm"></i> Paramétrage</a>
        <a href="{{ route('tresorerie.journaux') }}" class="sub-item {{ $route === 'tresorerie.journaux' ? 'active' : '' }}"><i class="ri-book-3-line text-sm"></i> Journaux</a>
        <a href="{{ route('tresorerie.grandLivre') }}" class="sub-item {{ $route === 'tresorerie.grandLivre' ? 'active' : '' }}"><i class="ri-scales-line text-sm"></i> Grand Livre</a>
        <a href="{{ route('tresorerie.exportSage') }}" class="sub-item {{ $route === 'tresorerie.exportSage' ? 'active' : '' }}"><i class="ri-upload-cloud-line text-sm"></i> Export Sage</a>
    </div>
</div>
@endif

{{-- ── Documents ─────────────────────────────────────────── --}}
<a href="{{ route('documents.index') }}" class="sidebar-item {{ str_starts_with($route, 'documents') ? 'active' : '' }}">
    <i class="ri-file-copy-2-fill text-lg"></i>
    <span>Documents</span>
</a>

{{-- ── Abonnements ───────────────────────────────────────── --}}
<a href="{{ route('abonnements.index') }}" class="sidebar-item {{ str_starts_with($route, 'abonnements') ? 'active' : '' }}">
    <i class="ri-vip-crown-fill text-lg"></i>
    <span>Abonnements</span>
</a>

{{-- ── Gestion de Stock ──────────────────────────────────── --}}
<a href="{{ route('stocks.index') }}" class="sidebar-item {{ $route === 'stocks.index' ? 'active' : '' }}">
    <i class="ri-store-3-fill text-lg"></i>
    <span>Gestion de Stock</span>
</a>

{{-- ── Achats ────────────────────────────────────────────── --}}
@if($canAchats)
<div x-data="{ open: {{ $isAchats ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-item w-full {{ $isAchats ? 'active' : '' }}">
        <i class="ri-shopping-cart-fill text-lg"></i>
        <span class="flex-1 text-left">Achats</span>
        <i class="ri-arrow-right-s-line text-base transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
    </button>
    <div x-show="open" x-collapse>
        <a href="{{ route('achats.index', ['tab' => 0]) }}" class="sub-item {{ $route === 'achats.index' && request('tab') == 0 ? 'active' : '' }}"><i class="ri-receipt-line text-sm"></i> Bons de Commande</a>
        <a href="{{ route('achats.index', ['tab' => 1]) }}" class="sub-item {{ $route === 'achats.index' && request('tab') == 1 ? 'active' : '' }}"><i class="ri-inbox-archive-line text-sm"></i> Bons de Réception</a>
        <a href="{{ route('achats.index', ['tab' => 2]) }}" class="sub-item {{ $route === 'achats.index' && request('tab') == 2 ? 'active' : '' }}"><i class="ri-bill-line text-sm"></i> Factures Fournisseurs</a>
        <a href="{{ route('achats.index', ['tab' => 3]) }}" class="sub-item {{ $route === 'achats.index' && request('tab') == 3 ? 'active' : '' }}"><i class="ri-radar-line text-sm"></i> Suivi des Commandes</a>
        <a href="{{ route('achats.index', ['tab' => 4]) }}" class="sub-item {{ $route === 'achats.index' && request('tab') == 4 ? 'active' : '' }}"><i class="ri-truck-line text-sm"></i> Fournisseurs</a>
        <a href="{{ route('achats.index', ['tab' => 5]) }}" class="sub-item {{ $route === 'achats.index' && request('tab') == 5 ? 'active' : '' }}"><i class="ri-pen-nib-line text-sm"></i> Signatures électroniques</a>
    </div>
</div>
@endif

{{-- ── GED ───────────────────────────────────────────────── --}}
@if($canGed)
<a href="{{ route('ged.index') }}" class="sidebar-item {{ $route === 'ged.index' ? 'active' : '' }}">
    <i class="ri-folder-2-fill text-lg"></i>
    <span>GED</span>
</a>
@endif

{{-- ── Paramètres ────────────────────────────────────────── --}}
<a href="{{ route('parametres.index') }}" class="sidebar-item {{ $route === 'parametres.index' ? 'active' : '' }}">
    <i class="ri-settings-4-fill text-lg"></i>
    <span>Paramètres</span>
</a>
