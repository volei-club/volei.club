{{-- ====== Game Management Modal ====== --}}
<div x-show="$store.gameModal.show" style="display:none" @keydown.escape.window="$store.gameModal.show = false"
     class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div @click.outside="$store.gameModal.show = false" class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[95vh]">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between shrink-0">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white" x-text="!$store.gameModal.canModifyMatches() ? 'Deltalii Meci' : ($store.gameModal.editingId ? 'Editează Meci' : 'Adaugă Meci Nou')"></h2>
            <button @click="$store.gameModal.show = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        {{-- Form Content --}}
        <form @submit.prevent="$store.gameModal.save()" class="overflow-y-auto flex-1 p-6 space-y-6">
            
            {{-- Basic Info Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 shadow-sm block">Echipa Adversă</label>
                    <input type="text" x-model="$store.gameModal.formData.opponent_name" required placeholder="ex: CSM București" :readonly="!$store.gameModal.canModifyMatches()"
                           class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary shadow-sm disabled:opacity-70">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 shadow-sm block">Locație</label>
                    <input type="text" x-model="$store.gameModal.formData.location" required placeholder="ex: Bacău, Sala Sporturilor" :readonly="!$store.gameModal.canModifyMatches()"
                           class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary shadow-sm disabled:opacity-70">
                </div>
            </div>

            {{-- Time & Squad Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 shadow-sm block">Data și Ora</label>
                    <input type="datetime-local" x-model="$store.gameModal.formData.match_date" required :readonly="!$store.gameModal.canModifyMatches()"
                           class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-1 focus:ring-primary shadow-sm bg-white dark:bg-slate-800 disabled:opacity-70">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 shadow-sm block">Grupa / Echipa Ta</label>
                    <select x-model="$store.gameModal.formData.squad_id" @change="$store.gameModal.fetchMembers($event.target.value)" required :disabled="!$store.gameModal.canModifyMatches()"
                            class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary shadow-sm disabled:opacity-70">
                        <option value="">Selectează Grupa</option>
                        <template x-for="sq in $store.gameModal.availableSquads" :key="sq.id">
                            <option :value="sq.id" x-text="sq.name"></option>
                        </template>
                    </select>
                </div>
            </div>

            <hr class="border-slate-100 dark:border-slate-700">

            {{-- Player Selection --}}
            <div x-show="$store.gameModal.formData.squad_id">
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">groups</span>
                    Componența Echipei
                </h4>
                
                <div x-show="$store.gameModal.loadingMembers" class="py-4 text-center">
                    <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin inline-block"></div>
                </div>

                <div x-show="!$store.gameModal.loadingMembers" class="max-h-[250px] overflow-y-auto space-y-2 pr-2">
                    <template x-for="user in $store.gameModal.members" :key="user.id">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-3">
                                <template x-if="user.photo">
                                    <img :src="'/storage/' + user.photo" class="w-8 h-8 rounded-full object-cover shrink-0">
                                </template>
                                <template x-if="!user.photo">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs" x-text="user.name.charAt(0)"></div>
                                </template>
                                <span class="text-sm font-medium" x-text="user.name"></span>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="$store.gameModal.togglePlayer(user.id, 'titular')" :disabled="!$store.gameModal.canModifyMatches()"
                                        :class="$store.gameModal.formData.starters.includes(user.id) ? 'bg-indigo-500 text-white border-indigo-500 shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700'"
                                        class="px-3 py-1.5 rounded-lg border text-[10px] font-black uppercase transition-all disabled:opacity-70">
                                    Titular
                                </button>
                                <button type="button" @click="$store.gameModal.togglePlayer(user.id, 'rezerva')" :disabled="!$store.gameModal.canModifyMatches()"
                                        :class="$store.gameModal.formData.substitutes.includes(user.id) ? 'bg-slate-400 text-white border-slate-400 shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700'"
                                        class="px-3 py-1.5 rounded-lg border text-[10px] font-black uppercase transition-all disabled:opacity-70">
                                    Rezervă
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <hr class="border-slate-100 dark:border-slate-700">

            {{-- Scores --}}
            <div>
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">score</span>
                    Scor pe Seturi
                </h4>
                
                <div class="bg-indigo-50/50 dark:bg-indigo-900/10 p-4 rounded-2xl border border-indigo-100 dark:border-indigo-900/30 space-y-4">
                    <template x-for="i in [1,2,3,4,5]" :key="i">
                        <div class="flex items-center gap-4">
                            <span class="text-xs font-bold text-indigo-400 w-10 text-center" x-text="'Set ' + i"></span>
                            <div class="flex-1 grid grid-cols-2 gap-3 items-center">
                                <div class="relative">
                                    <input type="number" x-model="$store.gameModal.formData['set'+i+'_home']" placeholder="Noi" :readonly="!$store.gameModal.canModifyMatches()"
                                           class="w-full pl-8 pr-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-center font-bold outline-none focus:ring-2 focus:ring-primary shadow-sm disabled:opacity-70">
                                    <div class="absolute left-2 top-1/2 -translate-y-1/2 text-[7px] font-black text-slate-300 uppercase leading-none">HOME</div>
                                </div>
                                <div class="relative">
                                    <input type="number" x-model="$store.gameModal.formData['set'+i+'_away']" placeholder="Ei" :readonly="!$store.gameModal.canModifyMatches()"
                                           class="w-full pl-8 pr-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-center font-bold outline-none focus:ring-2 focus:ring-primary shadow-sm disabled:opacity-70">
                                    <div class="absolute left-2 top-1/2 -translate-y-1/2 text-[7px] font-black text-slate-300 uppercase leading-none">AWAY</div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 shadow-sm block">Observații</label>
                <textarea x-model="$store.gameModal.formData.notes" rows="2" :readonly="!$store.gameModal.canModifyMatches()"
                          class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary shadow-sm resize-none disabled:opacity-70"></textarea>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 pt-2">
                <button type="button" @click="$store.gameModal.show = false"
                        class="flex-1 px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900 transition-all">
                    Anulează
                </button>
                <button type="submit" :disabled="$store.gameModal.saving" x-show="$store.gameModal.canModifyMatches()"
                        class="flex-[2] px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 disabled:opacity-50 flex items-center justify-center gap-2">
                    <template x-if="$store.gameModal.saving">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    </template>
                    <span x-text="$store.gameModal.editingId ? 'Salvează Modificări' : 'Creează Meci'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
