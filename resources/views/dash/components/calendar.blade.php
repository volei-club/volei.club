{{-- Calendar Page Component --}}
<div
    x-show="currentPage.startsWith('/dash/calendar')"
    x-data="calendarManager()"
    x-init="$watch('currentPage', (val) => { if (val.startsWith('/dash/calendar')) { fetchSessions(); } }); if (currentPage.startsWith('/dash/calendar')) { init(); }"
    class="w-full"
    style="display:none"
>
    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Calendar Antrenamente</h1>
            <p class="text-slate-500 text-sm mt-1">Sesiunile tale săptămânale și istoricul de prezență</p>
        </div>

        {{-- Parent: child selector --}}
        <div class="flex items-center gap-3">
            <template x-if="user?.role === 'parinte' && children.length > 1">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400">child_care</span>
                    <select x-model="selectedChildId" @change="fetchSessions()" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary">
                        <template x-for="child in children" :key="child.id">
                            <option :value="child.id" x-text="child.name"></option>
                        </template>
                    </select>
                </div>
            </template>

            <template x-if="canMarkAttendance()">
                <button @click="openGameModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-[18px]">sports_volleyball</span>
                    Adaugă Meci
                </button>
            </template>
        </div>
    </div>

    {{-- Week Navigation --}}
    <div class="mb-4 flex items-center justify-between bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 px-6 py-3 shadow-sm">
        <button @click="prevWeek()" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
            <span class="material-symbols-outlined text-slate-500">chevron_left</span>
        </button>
        <div class="text-center">
            <p class="font-bold text-slate-900 dark:text-white" x-text="weekLabel"></p>
        </div>
        <button @click="nextWeek()" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
            <span class="material-symbols-outlined text-slate-500">chevron_right</span>
        </button>
    </div>

    {{-- Loading State --}}
    <div x-show="loading" class="flex items-center justify-center py-20">
        <div class="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
    </div>

    {{-- Weekly Grid --}}
    <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-7 gap-3 mb-6">
        <template x-for="day in weekDays" :key="day.date">
            <div class="rounded-2xl border overflow-hidden"
                 :class="day.isToday ? 'border-primary/50 shadow-md shadow-primary/10' : 'border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800'">

                {{-- Day Header --}}
                <div class="px-3 py-2 text-center border-b border-slate-100 dark:border-slate-700"
                     :class="day.isToday ? 'bg-primary text-white' : 'bg-slate-50 dark:bg-slate-900'">
                    <p class="text-xs font-bold uppercase tracking-wider" x-text="day.label"></p>
                    <p class="text-lg font-black mt-0.5" x-text="new Date(day.date + 'T00:00:00').getDate()"></p>
                </div>

                {{-- Sessions --}}
                <div class="p-2 space-y-2 min-h-[80px]">
                    <template x-if="sessionsForDay(day.date).length === 0">
                        <p class="text-xs text-slate-300 dark:text-slate-600 text-center py-3">—</p>
                    </template>

                    <template x-for="session in sessionsForDay(day.date)" :key="session.id">
                        <div
                            @click="if (canMarkAttendance()) { if (session.type === 'game') { openGameModal(session); } else { openAttendance(session); } } else if (session.type === 'game') { openGameModal(session); }"
                            class="rounded-xl border px-2 py-2 text-xs transition-all"
                            :class="[statusColor(session), (canMarkAttendance() || session.type === 'game') ? 'cursor-pointer hover:shadow-md hover:scale-[1.02]' : '']"
                        >
                            <div x-show="session.type === 'game'" class="px-1.5 py-0.5 bg-indigo-500 text-white rounded text-[8px] font-black uppercase tracking-tighter w-fit mb-1">MECI</div>

                            <div class="flex items-center gap-1 font-bold mb-1">
                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                <span x-text="(session.start_time || '').slice(0,5) + (session.type !== 'game' ? ' – ' + (session.end_time || '').slice(0,5) : '')"></span>
                            </div>

                            <div x-show="session.type === 'game'">
                                <p class="font-black text-indigo-900 dark:text-indigo-200 uppercase truncate">VS <span x-text="session.opponent"></span></p>
                                <div x-show="session.score" class="mt-1 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px] text-indigo-500">analytics</span>
                                    <span class="font-black text-indigo-700 bg-white/50 px-1.5 rounded" x-text="session.score"></span>
                                </div>
                            </div>
                            
                            <div x-show="session.type !== 'game'">
                                <p class="font-semibold truncate" x-text="session.squad ?? session.team ?? 'Antrenament'"></p>
                            </div>

                            <p class="text-[10px] opacity-70 truncate" x-text="session.location ?? ''"></p>

                            <div x-show="!canMarkAttendance() && session.type === 'training'" class="mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]" x-text="statusIcon(session.status)"></span>
                                <span class="font-bold" x-text="statusLabel(session.status)"></span>
                            </div>

                            <div x-show="!canMarkAttendance() && session.type === 'game' && session.role" class="mt-1.5 flex items-center gap-1 text-[10px] font-bold text-indigo-600">
                                <span class="material-symbols-outlined text-[14px]">person</span>
                                <span x-text="session.role === 'titular' ? 'Titular' : 'Rezervă'"></span>
                            </div>

                            <div x-show="canMarkAttendance()" class="mt-1.5 flex items-center gap-1 text-[10px] opacity-60">
                                <span class="material-symbols-outlined text-[12px]" x-text="session.type === 'game' ? 'edit' : 'how_to_reg'"></span>
                                <span x-text="session.type === 'game' ? 'Detalii meci' : 'Bifează prezența'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty state --}}
    <div x-show="!loading && sessions.length === 0" class="text-center py-20 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700">
        <span class="material-symbols-outlined text-5xl text-slate-300 mb-3">calendar_today</span>
        <p class="text-slate-500">Nu există activități programate.</p>
        <p class="text-slate-400 text-sm mt-1">Contactează-ți antrenorul sau managerul.</p>
    </div>

    {{-- ====== Attendance Modal (Coach view) ====== --}}
    <div x-show="showAttendanceModal" style="display:none" @keydown.escape.window="closeAttendance()"
         class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div @click.outside="closeAttendance()" class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 shrink-0">
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white text-lg">Prezență Antrenament</h2>
                    <template x-if="attendanceSession">
                        <p class="text-sm text-slate-500 mt-0.5">
                            <span x-text="new Date(attendanceSession.date + 'T00:00:00').toLocaleDateString('ro-RO', {weekday:'long', day:'numeric', month:'long'})"></span>
                            &bull;
                            <span x-text="attendanceSession.start_time?.slice(0,5)"></span>–<span x-text="attendanceSession.end_time?.slice(0,5)"></span>
                        </p>
                    </template>
                </div>
                <button @click="closeAttendance()" class="text-slate-400 hover:text-slate-600 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl p-2 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            {{-- Members list --}}
            <div class="overflow-y-auto flex-1 px-6 py-4">

                <div x-show="loadingAttendance" class="flex items-center justify-center py-10">
                    <div class="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
                </div>

                <div x-show="!loadingAttendance && attendanceMembers.length === 0" class="text-center py-10 text-slate-400">
                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">person_off</span>
                    <p>Nicio sportivă asociată acestei echipe.</p>
                </div>

                <div x-show="!loadingAttendance" class="space-y-3">
                    <template x-for="member in attendanceMembers" :key="member.user_id">
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">

                            {{-- Avatar --}}
                            <template x-if="member.photo">
                                <img :src="'/storage/' + member.photo" class="w-10 h-10 rounded-full object-cover shrink-0">
                            </template>
                            <template x-if="!member.photo">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold shrink-0">
                                    <span x-text="member.name?.charAt(0)?.toUpperCase()"></span>
                                </div>
                            </template>

                            {{-- Name --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-900 dark:text-white text-sm truncate" x-text="member.name"></p>
                                <template x-if="member.status">
                                    <p class="text-xs text-slate-500" x-text="statusLabel(member.status)"></p>
                                </template>
                            </div>

                            {{-- Status Buttons --}}
                            <div class="flex items-center gap-1.5 shrink-0" x-show="!savingAttendance[member.user_id]">
                                {{-- Present --}}
                                <button
                                    @click="markAttendance(member, 'prezent')"
                                    :class="member.status === 'prezent' ? 'bg-emerald-500 text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:text-emerald-600'"
                                    class="w-9 h-9 rounded-xl flex items-center justify-center transition-all"
                                    title="Prezent"
                                >
                                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                </button>
                                {{-- Motivated --}}
                                <button
                                    @click="markAttendance(member, 'motivat')"
                                    :class="member.status === 'motivat' ? 'bg-amber-500 text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 hover:text-amber-600'"
                                    class="w-9 h-9 rounded-xl flex items-center justify-center transition-all"
                                    title="Motivat"
                                >
                                    <span class="material-symbols-outlined text-[20px]">info</span>
                                </button>
                                {{-- Absent --}}
                                <button
                                    @click="markAttendance(member, 'absent')"
                                    :class="member.status === 'absent' ? 'bg-red-500 text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600'"
                                    class="w-9 h-9 rounded-xl flex items-center justify-center transition-all"
                                    title="Absent"
                                >
                                    <span class="material-symbols-outlined text-[20px]">cancel</span>
                                </button>
                            </div>

                            {{-- Saving indicator --}}
                            <div x-show="savingAttendance[member.user_id]" class="w-9 h-9 flex items-center justify-center">
                                <div class="w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Summary footer --}}
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 rounded-b-2xl" x-show="!loadingAttendance && attendanceMembers.length > 0">
                <div class="flex justify-around text-center">
                    <div>
                        <p class="text-2xl font-black text-emerald-500" x-text="attendanceMembers.filter(m => m.status === 'prezent').length"></p>
                        <p class="text-xs text-slate-500">Prezenți</p>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-amber-500" x-text="attendanceMembers.filter(m => m.status === 'motivat').length"></p>
                        <p class="text-xs text-slate-500">Motivați</p>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-red-500" x-text="attendanceMembers.filter(m => m.status === 'absent').length"></p>
                        <p class="text-xs text-slate-500">Absenți</p>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-slate-400" x-text="attendanceMembers.filter(m => !m.status).length"></p>
                        <p class="text-xs text-slate-500">Neînregistrați</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
