<x-app-layout title="Inscriptions — Académique">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none;transition:all .15s}
.f-input:focus{background:#fff;box-shadow:0 0 0 2px var(--primary)44}
.tbl-th{font-size:11px;font-weight:600;color:#94A3B8;text-transform:uppercase;letter-spacing:.07em;padding:11px 16px;text-align:left}
.tbl-td{padding:13px 16px;font-size:13px;color:#475569;vertical-align:middle}
.act-btn{width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;cursor:pointer;border:none;background:transparent}
.page-btn{width:32px;height:32px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s}
.tool-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:12px;font-size:13px;font-weight:600;border:1px solid #E2E8F0;background:#fff;color:#475569;cursor:pointer;transition:all .15s;text-decoration:none}
.tool-btn:hover{background:#F8FAFC}
.filter-select{display:flex;align-items:center;gap:8px;padding:9px 14px;border-radius:12px;border:1px solid #E2E8F0;background:#fff;font-size:13px;font-weight:600;color:#475569;outline:none;cursor:pointer}
.badge{display:inline-flex;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700}
.ss-drop{position:absolute;z-index:20;margin-top:6px;width:100%;max-height:220px;overflow-y:auto;background:#fff;border:1px solid #E2E8F0;border-radius:10px;box-shadow:0 10px 25px rgba(0,0,0,.08);padding:4px}
.ss-item{padding:9px 12px;font-size:13px;border-radius:8px;cursor:pointer;color:#334155}
.ss-item:hover{background:#F1F5F9}
.ss-sel{background:rgba(90,103,216,.1);color:var(--primary);font-weight:600}
.toggle-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;border-radius:12px;border:1px solid #E2E8F0;background:#F8FAFC}
.toggle-switch{width:42px;height:23px;border-radius:999px;position:relative;cursor:pointer;transition:background .15s;flex-shrink:0}
.toggle-switch .dot{position:absolute;top:2px;left:2px;width:19px;height:19px;border-radius:50%;background:#fff;transition:left .15s;box-shadow:0 1px 3px rgba(0,0,0,.25)}
</style>
@endpush

@php
$niveauxData = $niveaux->map(fn($n) => ['v'=>$n->id,'l'=>$n->code ?: $n->libelle]);
$niveauxModalData = $niveaux->map(fn($n) => ['v'=>$n->id,'l'=>$n->code ? $n->libelle.' ('.$n->code.')' : $n->libelle]);
$filieresData = $filieres->map(fn($f) => ['v'=>$f->id,'l'=>$f->libelle]);
$classesData = $classes->map(fn($c) => ['v'=>$c->id,'l'=>$c->libelle,'niveau'=>$c->id_niveau,'filiere'=>$c->id_filiere]);
$etudiantsData = $etudiants->map(fn($e) => ['v'=>$e->id,'l'=>$e->nom.' '.$e->prenom.' — '.$e->matricule]);
$boursesData = $bourses->map(fn($b) => ['v'=>$b->id,'l'=>$b->libelle]);
@endphp

<div x-data="page({{ $inscriptions }}, {{ $niveauxData }}, {{ $classesData }}, {{ $etudiantsData }}, {{ $niveauxModalData }}, {{ $filieresData }}, {{ $boursesData }})" class="space-y-5">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold" style="color:#1E293B">Inscriptions</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="filtered.length"></span> inscription(s)</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('inscriptions.export') }}" class="tool-btn">
                <i class="ri-download-2-line"></i> Exporter
            </a>
            <button @click="openCreate()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-90" style="background:var(--primary)">
                <i class="ri-add-line"></i> Nouvelle Inscription
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:rgba(34,197,94,.08);color:#15803d;border:1px solid rgba(34,197,94,.18)">
        <i class="ri-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium" style="background:rgba(239,68,68,.08);color:#b91c1c;border:1px solid rgba(239,68,68,.18)">
        <i class="ri-error-warning-fill"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Filtres niveau / classe / recherche --}}
    <div class="flex items-center gap-3 flex-wrap">
        <div class="relative">
            <select x-model="selectedNiveau" @change="selectedClasse=''; currentPage=1" class="filter-select">
                <option value="">Tous les niveaux</option>
                <template x-for="n in niveaux" :key="n.v">
                    <option :value="n.v" x-text="n.l"></option>
                </template>
            </select>
        </div>
        <div class="relative">
            <select x-model="selectedClasse" @change="currentPage=1" class="filter-select">
                <option value="">Toutes les classes</option>
                <template x-for="c in classesForNiveau" :key="c.v">
                    <option :value="c.v" x-text="c.l"></option>
                </template>
            </select>
        </div>
        <div class="relative flex-1" style="min-width:220px">
            <i class="ri-search-line absolute left-4 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1"></i>
            <input x-model="search" @input="currentPage=1" type="text" placeholder="Rechercher nom, prénom, matric..." class="f-input" style="padding:12px 12px 12px 38px">
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100">
            <span class="text-xs font-medium" style="color:#94A3B8">Lignes/page :</span>
            <select x-model.number="perPage" @change="currentPage=1" class="filter-select" style="padding:6px 10px">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
            </select>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr style="border-bottom:1px solid #F1F5F9;background:#FAFBFC">
                <th class="tbl-th">N°</th>
                <th class="tbl-th" style="width:18%">Étudiant</th>
                <th class="tbl-th">Filière</th>
                <th class="tbl-th">Niveau</th>
                <th class="tbl-th">Classe</th>
                <th class="tbl-th">Type</th>
                <th class="tbl-th">Statut paiement</th>
                <th class="tbl-th">Bourse</th>
                <th class="tbl-th">Date</th>
                <th class="tbl-th text-right" style="width:130px">Actions</th>
            </tr></thead>
            <tbody>
                <template x-if="paginated.length===0">
                    <tr><td colspan="10" class="py-20 text-center">
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#F1F5F9"><i class="ri-user-add-line text-3xl" style="color:#CBD5E1"></i></div>
                        <p class="text-sm font-semibold" style="color:#64748B">Aucune inscription trouvée</p>
                    </td></tr>
                </template>
                <template x-for="r in paginated" :key="r.id">
                    <tr style="border-bottom:1px solid #F8FAFC" class="hover:bg-slate-50 transition-colors">
                        <td class="tbl-td">
                            <span class="font-bold text-[13px]" style="color:var(--primary)" x-text="r.numero_inscription"></span>
                        </td>
                        <td class="tbl-td">
                            <p class="font-semibold" style="color:#1E293B" x-text="r.etudiant_nom+' '+r.etudiant_prenom"></p>
                        </td>
                        <td class="tbl-td text-[12px]" x-text="r.filiere_libelle||'—'"></td>
                        <td class="tbl-td">
                            <span class="badge" style="background:#DCFCE7;color:#166534" x-text="r.niveau_libelle||'—'"></span>
                        </td>
                        <td class="tbl-td">
                            <span class="badge" style="background:#DBEAFE;color:#1d4ed8" x-text="r.classe_libelle||'—'"></span>
                        </td>
                        <td class="tbl-td">
                            <span class="badge" :style="typeStyle(r.type_inscription)" x-text="r.type_inscription||'—'"></span>
                        </td>
                        <td class="tbl-td text-[12px]" x-text="statutLabel(r.id_statut)"></td>
                        <td class="tbl-td text-[12px]" x-text="r.bourse?'Oui':'Non'"></td>
                        <td class="tbl-td text-[12px]" x-text="r.date_inscription||'—'"></td>
                        <td class="tbl-td">
                            <div class="flex items-center justify-end gap-1">
                                <a :href="'/etudiants/'+r.id_etudiant+'/fiche'" title="Voir l'étudiant" class="act-btn hover:bg-green-50" style="color:#16a34a"><i class="ri-file-text-line text-[15px]"></i></a>
                                <button @click="openEdit(r)" class="act-btn hover:bg-indigo-50" style="color:#94A3B8"><i class="ri-edit-2-line text-[15px]"></i></button>
                                <form :action="'/inscriptions/'+r.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer cette inscription ?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn hover:bg-red-50" style="color:#CBD5E1"><i class="ri-delete-bin-2-line text-[15px]"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        </div>
        <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100">
            <span class="text-xs" style="color:#94A3B8" x-text="info"></span>
            <div class="flex items-center gap-1">
                <button @click="currentPage--" :disabled="currentPage===1" class="page-btn hover:bg-gray-100 disabled:opacity-30" style="color:#64748B"><i class="ri-arrow-left-s-line"></i></button>
                <template x-for="p in pages" :key="p"><button @click="currentPage=p" class="page-btn" :style="currentPage===p?'background:var(--primary);color:#fff':'color:#475569'" :class="currentPage!==p?'hover:bg-gray-100':''" x-text="p"></button></template>
                <button @click="currentPage++" :disabled="currentPage===totalPages" class="page-btn hover:bg-gray-100 disabled:opacity-30" style="color:#64748B"><i class="ri-arrow-right-s-line"></i></button>
            </div>
        </div>
    </div>

    {{-- Modal création / édition --}}
    <template x-if="modal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(15,23,42,.5)" @click="modal=false">
            <div class="bg-white rounded-2xl shadow-2xl w-full" style="max-width:600px;max-height:90vh;overflow-y:auto" @click.stop>
                <div class="flex items-center gap-3 px-6 py-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(90,103,216,.12)"><i class="ri-user-add-fill text-xl" style="color:var(--primary)"></i></div>
                    <div class="flex-1"><h2 class="text-[15px] font-bold" style="color:#1E293B" x-text="editing?'Modifier l\'inscription':'Nouvelle Inscription'"></h2></div>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line text-lg" style="color:#94A3B8"></i></button>
                </div>
                <div style="height:1px;background:#F1F5F9;margin:0 24px"></div>
                <form :action="editing?'/inscriptions/'+form.id:'{{ route('inscriptions.store') }}'" method="POST" @submit="submitting=true">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="px-6 pt-5 pb-2 space-y-4">

                        {{-- Étudiant + création rapide --}}
                        <div>
                            <label class="f-label">Étudiant <span style="color:#EF4444">*</span></label>
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1" @click.outside="dropdownOpen.etudiant=false">
                                    <input type="hidden" name="id_etudiant" :value="form.id_etudiant">
                                    <div class="relative">
                                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1;pointer-events:none"></i>
                                        <input x-model="dropdownSearch.etudiant" @focus="dropdownOpen.etudiant=true" @input="dropdownOpen.etudiant=true; form.etudiant_label=''"
                                               :value="dropdownOpen.etudiant?dropdownSearch.etudiant:form.etudiant_label"
                                               type="text" class="f-input" style="padding-left:32px" placeholder="Rechercher..." autocomplete="off">
                                    </div>
                                    <div x-show="dropdownOpen.etudiant" class="ss-drop">
                                        <div x-show="!filteredEtudiants.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                        <template x-for="o in filteredEtudiants" :key="o.v">
                                            <div @click="selectField('etudiant', o); dropdownSearch.etudiant=''" class="ss-item" :class="form.id_etudiant===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                        </template>
                                    </div>
                                </div>
                                <button type="button" @click="showQuickAdd=!showQuickAdd" class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#F1F5F9;color:#475569">
                                    <i class="ri-add-line text-lg"></i>
                                </button>
                            </div>

                            {{-- Panneau de création rapide d'un étudiant --}}
                            <div x-show="showQuickAdd" class="mt-3 p-4 rounded-xl space-y-3" style="background:#F8FAFC;border:1px solid #E2E8F0">
                                <p class="text-xs font-semibold" style="color:#475569">Nouvel étudiant rapide</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <input x-model="quickEtudiant.nom" type="text" placeholder="Nom" class="f-input">
                                    <input x-model="quickEtudiant.prenom" type="text" placeholder="Prénom" class="f-input">
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <select x-model="quickEtudiant.sexe" class="f-input">
                                        <option value="">Sexe</option>
                                        <option value="M">Masculin</option>
                                        <option value="F">Féminin</option>
                                    </select>
                                    <input x-model="quickEtudiant.contact" type="text" placeholder="Contact">
                                </div>
                                <p x-show="quickError" class="text-xs" style="color:#EF4444" x-text="quickError"></p>
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="showQuickAdd=false" class="px-3 py-2 rounded-lg text-xs font-semibold" style="color:#64748B">Annuler</button>
                                    <button type="button" @click="quickCreateEtudiant()" :disabled="quickSaving" class="px-4 py-2 rounded-lg text-xs font-semibold text-white disabled:opacity-60" style="background:var(--primary)" x-text="quickSaving?'...':'Créer et sélectionner'"></button>
                                </div>
                            </div>
                        </div>

                        {{-- Filière + Niveau --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="f-label">Filière <span style="color:#EF4444">*</span></label>
                                <div class="relative" @click.outside="dropdownOpen.filiere=false">
                                    <input type="hidden" name="id_filiere" :value="form.id_filiere">
                                    <div class="relative">
                                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1;pointer-events:none"></i>
                                        <input x-model="dropdownSearch.filiere" @focus="dropdownOpen.filiere=true" @input="dropdownOpen.filiere=true; form.filiere_label=''"
                                               :value="dropdownOpen.filiere?dropdownSearch.filiere:form.filiere_label"
                                               type="text" class="f-input" style="padding-left:32px" placeholder="Rechercher..." autocomplete="off">
                                    </div>
                                    <div x-show="dropdownOpen.filiere" class="ss-drop">
                                        <div x-show="!filteredFilieres.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                        <template x-for="o in filteredFilieres" :key="o.v">
                                            <div @click="selectField('filiere', o); dropdownSearch.filiere=''" class="ss-item" :class="form.id_filiere===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="f-label">Niveau <span style="color:#EF4444">*</span></label>
                                <div class="relative" @click.outside="dropdownOpen.niveau=false">
                                    <input type="hidden" name="id_niveau" :value="form.id_niveau">
                                    <div class="relative">
                                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1;pointer-events:none"></i>
                                        <input x-model="dropdownSearch.niveau" @focus="dropdownOpen.niveau=true" @input="dropdownOpen.niveau=true; form.niveau_label=''"
                                               :value="dropdownOpen.niveau?dropdownSearch.niveau:form.niveau_label"
                                               type="text" class="f-input" style="padding-left:32px" placeholder="Rechercher..." autocomplete="off">
                                    </div>
                                    <div x-show="dropdownOpen.niveau" class="ss-drop">
                                        <div x-show="!filteredNiveaux.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                        <template x-for="o in filteredNiveaux" :key="o.v">
                                            <div @click="selectField('niveau', o); dropdownSearch.niveau=''" class="ss-item" :class="form.id_niveau===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Classe (filtrée par filière + niveau) --}}
                        <div>
                            <label class="f-label">Classe <span style="color:#EF4444">*</span></label>
                            <div class="relative" @click.outside="dropdownOpen.classe=false">
                                <input type="hidden" name="id_classe" :value="form.id_classe">
                                <div class="relative">
                                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1;pointer-events:none"></i>
                                    <input x-model="dropdownSearch.classe" @focus="dropdownOpen.classe=true" @input="dropdownOpen.classe=true; form.classe_label=''"
                                           :value="dropdownOpen.classe?dropdownSearch.classe:form.classe_label"
                                           type="text" class="f-input" style="padding-left:32px" placeholder="Rechercher..." autocomplete="off">
                                </div>
                                <div x-show="dropdownOpen.classe" class="ss-drop">
                                    <div x-show="!filteredClasses.length" class="ss-item" style="color:#94A3B8;cursor:default">
                                        <span x-show="!form.id_niveau && !form.id_filiere">Sélectionnez d'abord une filière/niveau</span>
                                        <span x-show="form.id_niveau || form.id_filiere">Aucune classe pour ce choix</span>
                                    </div>
                                    <template x-for="o in filteredClasses" :key="o.v">
                                        <div @click="selectField('classe', o); dropdownSearch.classe=''" class="ss-item" :class="form.id_classe===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Année scolaire (lecture seule) --}}
                        <div>
                            <label class="f-label">Année Scolaire</label>
                            <div class="flex items-center gap-2 px-3 py-2.5 rounded-xl" style="background:#F1F5F9">
                                <i class="ri-calendar-line text-sm" style="color:#94A3B8"></i>
                                <span class="text-sm font-medium" style="color:#475569">{{ $anneeActive->libelle ?? '—' }}</span>
                            </div>
                        </div>

                        {{-- Type + Statut paiement --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="f-label">Type</label>
                                <select name="type_inscription" x-model="form.type_inscription" class="f-input">
                                    <option value="Nouvelle">Nouvelle</option>
                                    <option value="Réinscription">Réinscription</option>
                                    <option value="Transfert">Transfert</option>
                                    <option value="Redoublement">Redoublement</option>
                                </select>
                            </div>
                            <div>
                                <label class="f-label">Statut paiement</label>
                                <select name="id_statut" x-model="form.id_statut" class="f-input">
                                    @foreach($statutsPaiement as $sp)
                                        <option value="{{ $sp->id }}">{{ $sp->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Date --}}
                        <div>
                            <label class="f-label">Date</label>
                            <input type="date" name="date_inscription" :value="form.date_inscription" class="f-input">
                        </div>

                        {{-- Affecté --}}
                        <div class="toggle-row">
                            <div class="flex items-center gap-2">
                                <i :class="form.affecte?'ri-checkbox-circle-fill':'ri-checkbox-blank-circle-line'" :style="form.affecte?'color:#1E293B':'color:#CBD5E1'"></i>
                                <span class="text-sm font-medium" style="color:#334155">Affecté</span>
                            </div>
                            <input type="hidden" name="affecte" :value="form.affecte?1:0">
                            <div class="toggle-switch" :style="form.affecte?'background:#1E293B':'background:#CBD5E1'" @click="form.affecte=!form.affecte">
                                <div class="dot" :style="form.affecte?'left:21px':'left:2px'"></div>
                            </div>
                        </div>

                        {{-- Boursier --}}
                        <div class="toggle-row">
                            <div class="flex items-center gap-2">
                                <i :class="form.boursier?'ri-checkbox-circle-fill':'ri-checkbox-blank-circle-line'" :style="form.boursier?'color:#1E293B':'color:#CBD5E1'"></i>
                                <span class="text-sm font-medium" style="color:#334155">L'étudiant est-il boursier ?</span>
                            </div>
                            <input type="hidden" name="boursier" :value="form.boursier?1:0">
                            <div class="toggle-switch" :style="form.boursier?'background:#1E293B':'background:#CBD5E1'" @click="form.boursier=!form.boursier; if(!form.boursier){form.id_bourse='';form.bourse_label=''}">
                                <div class="dot" :style="form.boursier?'left:21px':'left:2px'"></div>
                            </div>
                        </div>

                        {{-- Choix de la bourse --}}
                        <div x-show="form.boursier">
                            <label class="f-label">Bourse <span style="color:#EF4444">*</span></label>
                            <div class="relative" @click.outside="dropdownOpen.bourse=false">
                                <input type="hidden" name="id_bourse" :value="form.id_bourse">
                                <div class="relative">
                                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1;pointer-events:none"></i>
                                    <input x-model="dropdownSearch.bourse" @focus="dropdownOpen.bourse=true" @input="dropdownOpen.bourse=true; form.bourse_label=''"
                                           :value="dropdownOpen.bourse?dropdownSearch.bourse:form.bourse_label"
                                           type="text" class="f-input" style="padding-left:32px" placeholder="Rechercher une bourse..." autocomplete="off">
                                </div>
                                <div x-show="dropdownOpen.bourse" class="ss-drop">
                                    <div x-show="!filteredBourses.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucune bourse</div>
                                    <template x-for="o in filteredBourses" :key="o.v">
                                        <div @click="selectField('bourse', o); dropdownSearch.bourse=''" class="ss-item" :class="form.id_bourse===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                    </template>
                                </div>
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
function page(data, niveaux, classes, etudiants, niveauxModal, filieresList, boursesList){
    return {
        items:data, niveaux, classes, etudiants, niveauxModal, filieresList, boursesList,
        statutsPaiement: @json($statutsPaiement->map(fn($s) => ['v'=>$s->id,'l'=>$s->libelle])),
        search:'', selectedNiveau:'', selectedClasse:'', perPage:10, currentPage:1,
        modal:false, editing:false, submitting:false,
        showQuickAdd:false, quickSaving:false, quickError:'',
        quickEtudiant:{nom:'',prenom:'',sexe:'',contact:''},
        dropdownOpen:{etudiant:false,filiere:false,niveau:false,classe:false,bourse:false},
        dropdownSearch:{etudiant:'',filiere:'',niveau:'',classe:'',bourse:''},
        form:{},

        init(){
            this.form = this.blankForm();
        },

        blankForm(){
            return {
                id:'', id_etudiant:'', etudiant_label:'',
                id_filiere:'', filiere_label:'',
                id_niveau:'', niveau_label:'',
                id_classe:'', classe_label:'',
                type_inscription:'Nouvelle',
                id_statut: this.statutsPaiement?.[0]?.v ?? '',
                date_inscription:new Date().toISOString().slice(0,10),
                affecte:true, boursier:false,
                id_bourse:'', bourse_label:'',
            };
        },

        get classesForNiveau(){
            if(!this.selectedNiveau) return this.classes;
            return this.classes.filter(c => String(c.niveau) === String(this.selectedNiveau));
        },
        get filtered(){
            const q = this.search.toLowerCase();
            return this.items.filter(i => {
                if(this.selectedNiveau && String(i.id_niveau) !== String(this.selectedNiveau)) return false;
                if(this.selectedClasse && String(i.id_classe) !== String(this.selectedClasse)) return false;
                if(!q) return true;
                return (i.etudiant_nom+' '+i.etudiant_prenom).toLowerCase().includes(q)
                    || (i.matricule||'').toLowerCase().includes(q)
                    || (i.numero_inscription||'').toLowerCase().includes(q);
            });
        },
        get paginated(){ const s=(this.currentPage-1)*this.perPage; return this.filtered.slice(s,s+this.perPage); },
        get totalPages(){ return Math.max(1,Math.ceil(this.filtered.length/this.perPage)); },
        get pages(){ const p=[],t=this.totalPages,c=this.currentPage; for(let i=Math.max(1,c-2);i<=Math.min(t,c+2);i++)p.push(i); return p; },
        get info(){ if(!this.filtered.length)return '0 résultat(s)'; const s=(this.currentPage-1)*this.perPage+1,e=Math.min(this.currentPage*this.perPage,this.filtered.length); return `${s}–${e} sur ${this.filtered.length} résultat(s)`; },

        statutLabel(id){ const s=this.statutsPaiement.find(x=>String(x.v)===String(id)); return s?s.l:'—'; },

        filterList(list, q){ q=(q||'').toLowerCase(); return q ? list.filter(o=>o.l.toLowerCase().includes(q)) : list; },
        get filteredEtudiants(){ return this.filterList(this.etudiants, this.dropdownSearch.etudiant); },
        get filteredFilieres(){ return this.filterList(this.filieresList, this.dropdownSearch.filiere); },
        get filteredNiveaux(){ return this.filterList(this.niveauxModal, this.dropdownSearch.niveau); },
        get filteredClasses(){
            let list = this.classes;
            if(this.form.id_niveau)  list = list.filter(c => String(c.niveau)  === String(this.form.id_niveau));
            if(this.form.id_filiere) list = list.filter(c => String(c.filiere) === String(this.form.id_filiere));
            return this.filterList(list, this.dropdownSearch.classe);
        },
        get filteredBourses(){ return this.filterList(this.boursesList, this.dropdownSearch.bourse); },

        selectField(field, o){
            this.form['id_'+field] = String(o.v);
            this.form[field+'_label'] = o.l;
            this.dropdownOpen[field] = false;
            if(field==='niveau' || field==='filiere'){ this.form.id_classe=''; this.form.classe_label=''; }
        },

        openCreate(){ this.editing=false; this.submitting=false; this.showQuickAdd=false; this.quickError=''; this.form=this.blankForm(); this.modal=true; },
        openEdit(r){
            this.editing=true; this.submitting=false; this.showQuickAdd=false; this.quickError='';
            const etu = this.etudiants.find(e=>String(e.v)===String(r.id_etudiant));
            const fil = this.filieresList.find(f=>String(f.v)===String(r.id_filiere));
            const niv = this.niveauxModal.find(n=>String(n.v)===String(r.id_niveau));
            const cla = this.classes.find(c=>String(c.v)===String(r.id_classe));
            this.form = {
                id:r.id,
                id_etudiant:String(r.id_etudiant), etudiant_label: etu?etu.l:'',
                id_filiere:String(r.id_filiere), filiere_label: fil?fil.l:'',
                id_niveau:String(r.id_niveau), niveau_label: niv?niv.l:'',
                id_classe:String(r.id_classe), classe_label: cla?cla.l:'',
                type_inscription:r.type_inscription,
                id_statut:String(r.id_statut),
                date_inscription:r.date_inscription,
                affecte: !!r.affecte,
                boursier: !!r.bourse,
                id_bourse:'', bourse_label:'',
            };
            this.modal=true;
        },

        async quickCreateEtudiant(){
            if(!this.quickEtudiant.nom || !this.quickEtudiant.prenom){
                this.quickError = 'Nom et prénom requis.';
                return;
            }
            this.quickSaving = true;
            this.quickError = '';
            try{
                const res = await fetch('{{ route("inscriptions.etudiants.quick-create") }}', {
                    method:'POST',
                    headers:{
                        'Content-Type':'application/json',
                        'Accept':'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify(this.quickEtudiant),
                });
                const json = await res.json();
                if(!res.ok){
                    this.quickError = json.message || 'Erreur lors de la création.';
                    return;
                }
                const label = json.nom+' '+json.prenom+' — '+json.matricule;
                this.etudiants.push({v:json.id, l:label});
                this.selectField('etudiant', {v:json.id, l:label});
                this.showQuickAdd = false;
                this.quickEtudiant = {nom:'',prenom:'',sexe:'',contact:''};
            } catch(e){
                this.quickError = 'Erreur réseau.';
            } finally {
                this.quickSaving = false;
            }
        },
    }
}

function typeStyle(t){
    if(t==='Nouvelle')       return 'background:#DCFCE7;color:#166534';
    if(t==='Réinscription') return 'background:#DBEAFE;color:#1d4ed8';
    if(t==='Transfert')     return 'background:#EDE9FE;color:#6d28d9';
    if(t==='Redoublement')  return 'background:#FEE2E2;color:#b91c1c';
    return 'background:#F1F5F9;color:#64748B';
}
</script>
@endpush
</x-app-layout>
