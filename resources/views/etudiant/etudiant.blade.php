<x-app-layout title="Étudiants — Académique">
@push('styles')
<style>
.f-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:block}
.f-input{width:100%;padding:10px 12px;background:#F1F5F9;border:none;border-radius:8px;font-size:13px;color:#1E293B;outline:none;transition:all .15s}
.f-input:focus{background:#fff;box-shadow:0 0 0 2px var(--primary)44}
.tbl-th{font-size:11px;font-weight:600;color:#94A3B8;text-transform:uppercase;letter-spacing:.07em;padding:11px 16px;text-align:left}
.tbl-td{padding:13px 16px;font-size:13px;color:#475569;vertical-align:middle}
.act-btn{width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;cursor:pointer;border:none;background:transparent}
.page-btn{width:32px;height:32px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s}
.tool-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:12px;font-size:13px;font-weight:600;border:1px solid #E2E8F0;background:#fff;color:#475569;cursor:pointer;transition:all .15s}
.tool-btn:hover{background:#F8FAFC}
.filter-select{display:flex;align-items:center;gap:8px;padding:9px 14px;border-radius:12px;border:1px solid #E2E8F0;background:#fff;font-size:13px;font-weight:600;color:#475569;outline:none;cursor:pointer}
.avatar{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;background:rgba(90,103,216,.12);color:var(--primary);flex-shrink:0}
.ss-drop{position:absolute;z-index:20;margin-top:6px;width:100%;max-height:220px;overflow-y:auto;background:#fff;border:1px solid #E2E8F0;border-radius:10px;box-shadow:0 10px 25px rgba(0,0,0,.08);padding:4px}
.ss-item{padding:9px 12px;font-size:13px;border-radius:8px;cursor:pointer;color:#334155}
.ss-item:hover{background:#F1F5F9}
.ss-sel{background:rgba(90,103,216,.1);color:var(--primary);font-weight:600}
</style>
@endpush

@php
$niveauxData = $niveaux->map(fn($n) => ['v'=>$n->id,'l'=>$n->libelle]);
$classesData = $classes->map(fn($c) => ['v'=>$c->id,'l'=>$c->libelle,'niveau'=>$c->id_niveau]);
$parentsData = $parentsList->map(fn($p) => ['v'=>$p->id,'l'=>$p->nom.' '.$p->prenom]);
@endphp

<div x-data="page({{ $etudiants }}, {{ $niveauxData }}, {{ $classesData }}, {{ $parentsData }})" class="space-y-5">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold" style="color:#1E293B">Étudiants</h1>
            <p class="text-sm mt-0.5" style="color:#94A3B8"><span x-text="filtered.length"></span> étudiant(s) affiché(s)</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('etudiants.template') }}" class="tool-btn">
                <i class="ri-file-text-line"></i> Modèle CSV
            </a>
            <form :action="'{{ route('etudiants.import') }}'" method="POST" enctype="multipart/form-data" @submit="importing=true" x-ref="importForm">
                @csrf
                <label class="tool-btn" style="cursor:pointer">
                    <i class="ri-upload-2-line"></i>
                    <span x-text="importing?'Import...':'Importer CSV'"></span>
                    <input type="file" name="fichier" accept=".csv" class="hidden" @change="$refs.importForm.submit()">
                </label>
            </form>
            <a href="{{ route('etudiants.export') }}" class="tool-btn">
                <i class="ri-download-2-line"></i> Exporter CSV
            </a>
            <a href="{{ route('etudiants.export-pdf') }}" class="tool-btn">
                <i class="ri-file-pdf-2-line"></i> Exporter PDF
            </a>
            <button @click="openCreate()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-90" style="background:var(--primary)">
                <i class="ri-add-line"></i> Nouvel Étudiant
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

    {{-- Filtres niveau / classe --}}
    <div class="flex items-center gap-3">
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
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <div class="relative">
                <i class="ri-search-line absolute left-4 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1"></i>
                <input x-model="search" @input="currentPage=1" type="text" placeholder="Rechercher par nom ou matricule..." class="f-input" style="padding:12px 12px 12px 38px;background:#F8FAFC">
            </div>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr style="border-bottom:1px solid #F1F5F9;background:#FAFBFC">
                <th class="tbl-th">Matricule</th>
                <th class="tbl-th" style="width:22%">Étudiant</th>
                <th class="tbl-th">Genre</th>
                <th class="tbl-th">Date naiss.</th>
                <th class="tbl-th">Contact</th>
                <th class="tbl-th">Statut</th>
                <th class="tbl-th text-right" style="width:170px">Actions</th>
            </tr></thead>
            <tbody>
                <template x-if="paginated.length===0">
                    <tr><td colspan="7" class="py-20 text-center">
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#F1F5F9"><i class="ri-graduation-cap-line text-3xl" style="color:#CBD5E1"></i></div>
                        <p class="text-sm font-semibold" style="color:#64748B">Aucun étudiant trouvé</p>
                    </td></tr>
                </template>
                <template x-for="r in paginated" :key="r.id">
                    <tr style="border-bottom:1px solid #F8FAFC" class="hover:bg-slate-50 transition-colors">
                        <td class="tbl-td">
                            <a :href="'/etudiants/'+r.id+'/fiche'" class="font-bold text-[13px] hover:underline" style="color:var(--primary)" x-text="r.matricule"></a>
                        </td>
                        <td class="tbl-td">
                            <div class="flex items-center gap-3">
                                <div class="avatar" x-text="r.nom.charAt(0)"></div>
                                <div>
                                    <p class="font-semibold" style="color:#1E293B" x-text="r.nom+' '+r.prenom"></p>
                                    <p class="text-[11px]" style="color:#94A3B8" x-text="r.email||'—'"></p>
                                </div>
                            </div>
                        </td>
                        <td class="tbl-td">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold"
                                  :style="r.sexe==='M'?'background:rgba(59,130,246,.1);color:#2563eb':'background:rgba(219,39,119,.1);color:#db2777'"
                                  x-text="r.sexe==='M'?'Masculin':(r.sexe==='F'?'Féminin':'—')"></span>
                        </td>
                        <td class="tbl-td text-[12px]" x-text="r.date_naissance||'—'"></td>
                        <td class="tbl-td text-[12px]" x-text="r.contact||'—'"></td>
                        <td class="tbl-td">
                            <form :action="'/etudiants/'+r.id+'/statut'" method="POST" style="display:inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[11px] font-bold border-0 cursor-pointer hover:opacity-75 transition"
                                        :class="r.actif?'bg-green-100 text-green-700':'bg-red-100 text-red-600'">
                                    <span class="w-1.5 h-1.5 rounded-full inline-block" :style="'background:'+(r.actif?'#16a34a':'#dc2626')"></span>
                                    <span x-text="r.actif?'Actif':'Inactif'"></span>
                                </button>
                            </form>
                        </td>
                        <td class="tbl-td">
                            <div class="flex items-center justify-end gap-1">
                                <a :href="'/etudiants/'+r.id+'/fiche'" title="Voir le parent lié" class="act-btn hover:bg-indigo-50" style="color:#6366f1"><i class="ri-parent-line text-[15px]"></i></a>
                                <a :href="'/etudiants/'+r.id+'/fiche'" title="Fiche étudiant" class="act-btn hover:bg-green-50" style="color:#16a34a"><i class="ri-file-text-line text-[15px]"></i></a>
                                <a :href="'/etudiants/'+r.id+'/carte'" target="_blank" title="Carte scolaire (PDF)" class="act-btn hover:bg-orange-50" style="color:#ea580c"><i class="ri-share-forward-line text-[15px]"></i></a>
                                <a :href="'/etudiants/'+r.id+'/documents'" title="Dossier documents" class="act-btn hover:bg-amber-50" style="color:#d97706"><i class="ri-folder-3-line text-[15px]"></i></a>
                                <button @click="openEdit(r)" class="act-btn hover:bg-indigo-50" style="color:#94A3B8"><i class="ri-edit-2-line text-[15px]"></i></button>
                                <form :action="'/etudiants/'+r.id" method="POST" style="display:inline" @submit.prevent="if(confirm('Supprimer cet étudiant ?')) $el.submit()">
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
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(15,23,42,.5)">
            <div class="bg-white rounded-2xl shadow-2xl w-full" style="max-width:600px" @click.stop>
                <div class="flex items-center gap-3 px-6 py-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(90,103,216,.12)"><i class="ri-user-3-fill text-xl" style="color:var(--primary)"></i></div>
                    <div class="flex-1"><h2 class="text-[15px] font-bold" style="color:#1E293B" x-text="editing?'Modifier l\'étudiant':'Nouvel étudiant'"></h2></div>
                    <button @click="modal=false" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100"><i class="ri-close-line text-lg" style="color:#94A3B8"></i></button>
                </div>
                <div style="height:1px;background:#F1F5F9;margin:0 24px"></div>
                <form :action="editing?'/etudiants/'+form.id:'{{ route('etudiants.store') }}'" method="POST" @submit="submitting=true">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="px-6 pt-5 pb-2 space-y-4" style="max-height:60vh;overflow-y:auto">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="f-label">Nom <span style="color:#EF4444">*</span></label>
                                <input type="text" name="nom" :value="form.nom" required class="f-input" placeholder="Ex : WADE">
                            </div>
                            <div>
                                <label class="f-label">Prénom <span style="color:#EF4444">*</span></label>
                                <input type="text" name="prenom" :value="form.prenom" required class="f-input" placeholder="Ex : Abdoulaye">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="f-label">Sexe</label>
                                <div x-data="sSelect([{v:'M',l:'Masculin'},{v:'F',l:'Féminin'}], form.sexe, 'Sélectionner...')" class="relative" @click.outside="open=false">
                                    <input type="hidden" name="sexe" :value="v">
                                    <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" :placeholder="ph" autocomplete="off" readonly>
                                    <div x-show="open" class="ss-drop">
                                        <template x-for="o in filtered" :key="o.v">
                                            <div @click="select(o)" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="f-label">Date de naissance</label>
                                <input type="date" name="date_naissance" :value="form.date_naissance" class="f-input">
                            </div>
                            <div>
                                <label class="f-label">Lieu de naissance</label>
                                <input type="text" name="lieu_naissance" :value="form.lieu_naissance" class="f-input" placeholder="Ex : Dakar">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="f-label">Nationalité</label>
                                <input type="text" name="nationalite" :value="form.nationalite" class="f-input" placeholder="Ex : Sénégalaise">
                            </div>
                            <div>
                                <label class="f-label">Contact</label>
                                <input type="text" name="contact" :value="form.contact" class="f-input" placeholder="Ex : +221 77 000 00 00">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="f-label">Email</label>
                                <input type="email" name="email" :value="form.email" class="f-input" placeholder="exemple@email.com">
                            </div>
                            <div>
                                <label class="f-label">Parent / Tuteur</label>
                                <div x-data="sSelect(parentsList, form.id_parent, 'Rechercher un parent...')" class="relative" @click.outside="open=false">
                                    <input type="hidden" name="id_parent" :value="v">
                                    <div class="relative">
                                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#CBD5E1;pointer-events:none"></i>
                                        <input x-model="s" @focus="open=true" @input="open=true" type="text" class="f-input" style="padding-left:32px" :placeholder="ph" autocomplete="off">
                                    </div>
                                    <div x-show="open" class="ss-drop">
                                        <div x-show="!filtered.length" class="ss-item" style="color:#94A3B8;cursor:default">Aucun résultat</div>
                                        <template x-for="o in filtered" :key="o.v">
                                            <div @click="select(o)" class="ss-item" :class="v===String(o.v)?'ss-sel':''" x-text="o.l"></div>
                                        </template>
                                    </div>
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
function page(data, niveaux, classes, parentsList){
    return {
        items:data, niveaux, classes, parentsList,
        search:'', selectedNiveau:'', selectedClasse:'', perPage:10, currentPage:1,
        modal:false, editing:false, submitting:false, importing:false,
        form:{id:'',nom:'',prenom:'',sexe:'',date_naissance:'',lieu_naissance:'',nationalite:'',contact:'',email:'',id_parent:''},

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
                return (i.nom+' '+i.prenom).toLowerCase().includes(q) || (i.matricule||'').toLowerCase().includes(q);
            });
        },
        get paginated(){ const s=(this.currentPage-1)*this.perPage; return this.filtered.slice(s,s+this.perPage); },
        get totalPages(){ return Math.max(1,Math.ceil(this.filtered.length/this.perPage)); },
        get pages(){ const p=[],t=this.totalPages,c=this.currentPage; for(let i=Math.max(1,c-2);i<=Math.min(t,c+2);i++)p.push(i); return p; },
        get info(){ if(!this.filtered.length)return '0 résultat(s)'; const s=(this.currentPage-1)*this.perPage+1,e=Math.min(this.currentPage*this.perPage,this.filtered.length); return `${s}–${e} sur ${this.filtered.length} résultat(s)`; },
        openCreate(){ this.editing=false; this.submitting=false; this.form={id:'',nom:'',prenom:'',sexe:'',date_naissance:'',lieu_naissance:'',nationalite:'',contact:'',email:'',id_parent:''}; this.modal=true; },
        openEdit(r){ this.editing=true; this.submitting=false; this.form={...r}; this.modal=true; },
    }
}

function sSelect(options, initial, placeholder){
    return {
        options: options.map(o => o.l !== undefined ? o : {v:o.v, l:o.l}),
        v: initial||'', s:'', open:false, ph: placeholder,
        init(){ const found = this.options.find(o=>String(o.v)===String(this.v)); this.s = found?found.l:''; },
        get filtered(){ const q=this.s.toLowerCase(); return q?this.options.filter(o=>o.l.toLowerCase().includes(q)):this.options; },
        select(o){ this.v=o.v; this.s=o.l; this.open=false; }
    }
}
</script>
@endpush
</x-app-layout>
