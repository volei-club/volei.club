            <!-- HOME VIEW -->
            <div x-show="currentPage === '/dash'" x-data="homeManager()" class="space-y-6">

                <!-- Welcome + Date -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
                            {{ __('dash.welcome', ['name' => '']) }}<span x-text="user?.name?.split(` `)[0]"></span>! 👋
                        </h2>
                        <p class="text-sm text-slate-500 mt-0.5" x-text="new Date().toLocaleDateString(locale, { weekday: `long`, year: `numeric`, month: `long`, day: `numeric` })"></p>
                    </div>
                    <button @click="loadStats()" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-slate-600 dark:text-slate-400">
                        <span class="material-symbols-outlined text-[18px]" :class="loading && `animate-spin`">refresh</span>
                        {{ __('dash.refresh') }}
                    </button>
                </div>

                <!-- Loading -->
                <div x-show="loading" class="flex items-center gap-3 text-slate-500 text-sm">
                    <span class="material-symbols-outlined animate-spin text-primary">progress_activity</span>
                    {{ __('dash.loading') }}
                </div>

                <!-- KPI Cards for Administrators/Managers -->
                <template x-if="['administrator', 'manager', 'antrenor'].includes(user?.role)">
                    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

                        <!-- Card 1: Cluburi (admin) sau Grupe (manager) -->
                        <template x-if="user?.role === `administrator`">
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('dash.nav.clubs') }}</p>
                                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1" x-text="stats.kpi?.clubs ?? `-`"></h3>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                        <span class="material-symbols-outlined text-[22px]">apartment</span>
                                    </div>
                                </div>
                                <div class="h-10 w-full flex items-end gap-0.5">
                                    <template x-for="(val, idx) in (stats.trends?.clubs ?? [0,0,0,0,0,0])" :key="idx">
                                        <div class="flex-1 rounded-t transition-all duration-500"
                                            :class="idx === (stats.trends?.clubs ?? []).length - 1 ? `bg-primary` : `bg-primary/30`"
                                            :style="`height: ${barHeightPct(stats.trends?.clubs ?? [], idx)}%`"></div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template x-if="['manager', 'antrenor'].includes(user?.role)">
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('dash.nav.groups') }}</p>
                                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1" x-text="stats.kpi?.grupe ?? `-`"></h3>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                        <span class="material-symbols-outlined text-[22px]">groups</span>
                                    </div>
                                </div>
                                <div class="h-10 w-full flex items-end gap-0.5">
                                    <template x-for="(val, idx) in (stats.trends?.grupe ?? [0,0,0,0,0,0])" :key="idx">
                                        <div class="flex-1 rounded-t transition-all duration-500"
                                            :class="idx === (stats.trends?.grupe ?? []).length - 1 ? `bg-primary` : `bg-primary/30`"
                                            :style="`height: ${barHeightPct(stats.trends?.grupe ?? [], idx)}%`"></div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Card 2: Sportivi -->
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('dash.nav.athletes') }}</p>
                                    <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1" x-text="stats.kpi?.sportivi ?? `-`"></h3>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600">
                                    <span class="material-symbols-outlined text-[22px]">sports_handball</span>
                                </div>
                            </div>
                            <div class="h-10 w-full flex items-end gap-0.5">
                                <template x-for="(val, idx) in (stats.trends?.sportivi ?? [0,0,0,0,0,0])" :key="idx">
                                    <div class="flex-1 rounded-t transition-all duration-500"
                                        :class="idx === (stats.trends?.sportivi ?? []).length - 1 ? `bg-emerald-500` : `bg-emerald-300/60`"
                                        :style="`height: ${barHeightPct(stats.trends?.sportivi ?? [], idx)}%`"></div>
                                </template>
                            </div>
                        </div>

                        <!-- Card 3: Grupe (admin) sau Antrenamente (manager) -->
                        <template x-if="user?.role === `administrator`">
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('dash.nav.groups') }}</p>
                                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1" x-text="stats.kpi?.grupe ?? `-`"></h3>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center text-violet-600">
                                        <span class="material-symbols-outlined text-[22px]">diversity_3</span>
                                    </div>
                                </div>
                                <div class="h-10 w-full flex items-end gap-0.5">
                                    <template x-for="(val, idx) in (stats.trends?.grupe ?? [0,0,0,0,0,0])" :key="idx">
                                        <div class="flex-1 rounded-t transition-all duration-500"
                                            :class="idx === (stats.trends?.grupe ?? []).length - 1 ? `bg-violet-500` : `bg-violet-300/50`"
                                            :style="`height: ${barHeightPct(stats.trends?.grupe ?? [], idx)}%`"></div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template x-if="['manager', 'antrenor'].includes(user?.role)">
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('dash.nav.trainings') }}</p>
                                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1" x-text="stats.kpi?.antrenamente ?? `-`"></h3>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600">
                                        <span class="material-symbols-outlined text-[22px]">sports</span>
                                    </div>
                                </div>
                                <div class="h-10 w-full flex items-end gap-0.5">
                                    <template x-for="(val, idx) in (stats.trends?.antrenamente ?? [0,0,0,0,0,0])" :key="idx">
                                        <div class="flex-1 rounded-t transition-all duration-500"
                                            :class="idx === (stats.trends?.antrenamente ?? []).length - 1 ? `bg-amber-500` : `bg-amber-300/50`"
                                            :style="`height: ${barHeightPct(stats.trends?.antrenamente ?? [], idx)}%`"></div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Card 4: Abonamente -->
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('dash.nav.subscriptions') }}</p>
                                    <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1" x-text="stats.kpi?.abonamente ?? `-`"></h3>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-500">
                                    <span class="material-symbols-outlined text-[22px]">payments</span>
                                </div>
                            </div>
                            <div class="h-10 w-full flex items-end gap-0.5">
                                <template x-for="(val, idx) in (stats.trends?.abonamente ?? [0,0,0,0,0,0])" :key="idx">
                                    <div class="flex-1 rounded-t transition-all duration-500"
                                        :class="idx === (stats.trends?.abonamente ?? []).length - 1 ? `bg-rose-500` : `bg-rose-300/50`"
                                        :style="`height: ${barHeightPct(stats.trends?.abonamente ?? [], idx)}%`"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- KPI Cards for Athletes / Parents -->
                <template x-if="['sportiv', 'parinte'].includes(user?.role)">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        
                        <!-- Next Session Card -->
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60 flex flex-col justify-between">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('dash.athlete.next_session') }}</p>
                                    <template x-if="stats.athlete_stats?.next_session">
                                        <div class="mt-2">
                                            <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="stats.athlete_stats.next_session.title"></h3>
                                            <div class="flex items-center gap-2 text-primary font-bold mt-1">
                                                <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                                                <span x-text="stats.athlete_stats.next_session.date"></span>
                                            </div>
                                            <div class="flex items-center gap-2 text-slate-500 text-sm mt-1">
                                                <span class="material-symbols-outlined text-[18px]">schedule</span>
                                                <span x-text="stats.athlete_stats.next_session.time"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!stats.athlete_stats?.next_session">
                                        <p class="mt-2 text-slate-400 italic">{{ __('dash.athlete.no_session') }}</p>
                                    </template>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                                    <span class="material-symbols-outlined text-[28px]" x-text="stats.athlete_stats?.next_session?.type === 'match' ? 'sports_volleyball' : 'sports'"></span>
                                </div>
                            </div>
                            <template x-if="stats.athlete_stats?.next_session">
                                <div class="pt-3 border-t border-slate-50 dark:border-slate-700 flex items-center gap-2 text-xs text-slate-500 truncate">
                                    <span class="material-symbols-outlined text-[16px]">location_on</span>
                                    <span class="truncate" x-text="stats.athlete_stats.next_session.location || '{{ __('dash.athlete.unspecified_location') }}'"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Latest Performance Card -->
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('dash.athlete.latest_performance') }}</p>
                                    <template x-if="stats.athlete_stats?.latest_performance">
                                        <div class="mt-3 space-y-3">
                                            <div class="flex items-center justify-between gap-4">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                                                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('dash.athlete.vertical_jump') }}</span>
                                                </div>
                                                <span class="font-bold text-slate-900 dark:text-white" x-text="stats.athlete_stats.latest_performance.vertical_jump + ' {{ __('dash.units.cm') }}'"></span>
                                            </div>
                                            <div class="flex items-center justify-between gap-4">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('dash.athlete.serve_speed') }}</span>
                                                </div>
                                                <span class="font-bold text-slate-900 dark:text-white" x-text="stats.athlete_stats.latest_performance.serve_speed + ' {{ __('dash.units.kmh') }}'"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!stats.athlete_stats?.latest_performance">
                                        <p class="mt-2 text-slate-400 italic">{{ __('dash.no_data') }}</p>
                                    </template>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-500">
                                    <span class="material-symbols-outlined text-[28px]">monitoring</span>
                                </div>
                            </div>
                            <template x-if="stats.athlete_stats?.latest_performance">
                                <div class="pt-3 border-t border-slate-50 dark:border-slate-700 flex justify-between items-center text-[10px] font-bold text-slate-400 uppercase">
                                    <span>{{ __('dash.athlete.registered_at') }}</span>
                                    <span x-text="stats.athlete_stats.latest_performance.date"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Subscription Status Card -->
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('dash.athlete.subscription_status') }}</p>
                                    <template x-if="stats.athlete_stats?.subscription">
                                        <div class="mt-2">
                                            <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="stats.athlete_stats.subscription.plan_name"></h3>
                                            <div class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 uppercase tracking-wide">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                                {{ __('dash.athlete.active') }}
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!stats.athlete_stats?.subscription">
                                        <div class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 uppercase tracking-wide">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                            {{ __('dash.athlete.no_subscription') }}
                                        </div>
                                    </template>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-500">
                                    <span class="material-symbols-outlined text-[28px]">loyalty</span>
                                </div>
                            </div>
                            <template x-if="stats.athlete_stats?.subscription">
                                <div class="pt-3 border-t border-slate-50 dark:border-slate-700 flex justify-between items-center text-[10px] font-bold text-slate-400 uppercase">
                                    <span>{{ __('dash.athlete.expires_at') }}</span>
                                    <span x-text="stats.athlete_stats.subscription.expiry"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Main grid for Admins/Managers -->
                <template x-if="['administrator', 'manager', 'antrenor'].includes(user?.role)">
                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

                        <!-- Left: Recent Clubs (admin) or Recent Members (manager) -->
                        <section class="xl:col-span-8 bg-white dark:bg-slate-800 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60 overflow-hidden flex flex-col">
                            <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white" x-text="user?.role === `administrator` ? `{{ __('dash.sections.recent_clubs') }}` : `{{ __('dash.sections.recent_members') }}`"></h2>
                                    <p class="text-xs text-slate-500 mt-0.5" x-text="user?.role === `administrator` ? `{{ __('dash.sections.recent_clubs_desc') }}` : `{{ __('dash.sections.recent_members_desc') }}`"></p>
                                </div>
                                <button
                                    @click="user?.role === `administrator` ? navigate(`/dash/cluburi`) : navigate(`/dash/membri`)"
                                    class="px-3 py-1.5 text-xs font-bold text-primary bg-primary/5 rounded-lg hover:bg-primary/10 transition-colors">
                                    {{ __('dash.view_all') }}
                                </button>
                            </div>

                            <!-- Admin: Clubs table -->
                            <template x-if="user?.role === `administrator`">
                                <div class="overflow-x-auto flex-1">
                                    <table class="w-full text-left border-collapse text-sm">
                                        <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs uppercase text-slate-500 font-bold tracking-wider border-b border-slate-100 dark:border-slate-700">
                                            <tr>
                                                <th class="px-5 py-3">{{ __('dash.tables.club') }}</th>
                                                <th class="px-5 py-3 text-right">{{ __('dash.tables.registered') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                            <template x-if="!stats.recent_clubs?.length">
                                                <tr><td colspan="2" class="text-center py-10 text-slate-400 text-sm">{{ __('dash.tables.no_clubs') }}</td></tr>
                                            </template>
                                            <template x-for="club in stats.recent_clubs" :key="club.id">
                                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                                    <td class="px-5 py-3.5 font-semibold text-slate-900 dark:text-white" x-text="club.name"></td>
                                                    <td class="px-5 py-3.5 text-right text-slate-500" x-text="club.created_at"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <!-- Manager: Members table -->
                            <template x-if="user?.role !== `administrator`">
                                <div class="overflow-x-auto flex-1">
                                    <table class="w-full text-left border-collapse text-sm">
                                        <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs uppercase text-slate-500 font-bold tracking-wider border-b border-slate-100 dark:border-slate-700">
                                            <tr>
                                                <th class="px-5 py-3">{{ __('dash.tables.member') }}</th>
                                                <th class="px-5 py-3">{{ __('dash.tables.role') }}</th>
                                                <th class="px-5 py-3 text-right">{{ __('dash.tables.added') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                            <template x-if="!stats.recent_members?.length">
                                                <tr><td colspan="3" class="text-center py-10 text-slate-400 text-sm">{{ __('dash.tables.no_members') }}</td></tr>
                                            </template>
                                            <template x-for="m in stats.recent_members" :key="m.id">
                                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer" @click="navigate(`/dash/membri`)">
                                                    <td class="px-5 py-3.5">
                                                        <div class="flex items-center gap-3">
                                                            <template x-if="m.photo">
                                                                <img :src="`/storage/` + m.photo" class="w-8 h-8 rounded-lg object-cover border border-slate-100 dark:border-slate-700 shrink-0">
                                                            </template>
                                                            <template x-if="!m.photo">
                                                                <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-sm font-bold shrink-0" x-text="m.name?.charAt(0)"></div>
                                                            </template>
                                                            <span class="font-semibold text-slate-900 dark:text-white" x-text="m.name"></span>
                                                        </div>
                                                    </td>
                                                    <td class="px-5 py-3.5">
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                                                            :class="m.role === `antrenor` ? `bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400` : `bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400`"
                                                            x-text="m.role === `antrenor` ? `{{ __('dash.roles.antrenor') }}` : `{{ __('dash.roles.sportiv') }}`"></span>
                                                    </td>
                                                    <td class="px-5 py-3.5 text-right text-slate-500" x-text="m.created_at"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                        </section>

                        <!-- Right: Recent Subscriptions -->
                        <section class="xl:col-span-4 bg-white dark:bg-slate-800 p-5 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60 flex flex-col">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('dash.sections.recent_subscriptions') }}</h2>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ __('dash.sections.recent_payments_desc') }}</p>
                                </div>
                                <button @click="navigate(`/dash/abonamente`)" class="px-3 py-1.5 text-xs font-bold text-primary bg-primary/5 rounded-lg hover:bg-primary/10 transition-colors">
                                    {{ __('dash.view_all') }}
                                </button>
                            </div>

                            <div class="space-y-3 flex-1">
                                <template x-if="!stats.recent_subscriptions?.length">
                                    <div class="py-12 text-center text-slate-400 text-sm">
                                        <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2">payments</span>
                                        <p>{{ __('dash.tables.no_subscriptions') }}</p>
                                    </div>
                                </template>
                                <template x-for="sub in stats.recent_subscriptions" :key="sub.id">
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700/50">
                                        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                                            :class="isActiv(sub.status) ? `bg-emerald-100 dark:bg-emerald-900/30` : sub.status === `CANCELLED` || sub.status === `anulat` ? `bg-red-100 dark:bg-red-900/30` : `bg-amber-100 dark:bg-amber-900/30`">
                                            <span class="material-symbols-outlined text-[18px]"
                                                :class="isActiv(sub.status) ? `text-emerald-600` : sub.status === `CANCELLED` || sub.status === `anulat` ? `text-red-500` : `text-amber-600`"
                                                x-text="isActiv(sub.status) ? `check_circle` : sub.status === `CANCELLED` || sub.status === `anulat` ? `cancel` : `schedule`"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white truncate" x-text="sub.plan_name"></p>
                                            <p class="text-xs text-slate-500 truncate" x-text="sub.user_name"></p>
                                        </div>
                                        <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full shrink-0"
                                            :class="isActiv(sub.status) ? `bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400` : sub.status === `CANCELLED` || sub.status === `anulat` ? `bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400` : `bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400`"
                                            x-text="statusLabel(sub.status)"></span>
                                    </div>
                                </template>
                            </div>
                        </section>
                    </div>
                </template>

                <!-- Bottom grid -->
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

                    <!-- Recent Conversations -->
                    <section :class="['administrator', 'manager', 'antrenor'].includes(user?.role) ? 'xl:col-span-6' : 'xl:col-span-12'" class="bg-white dark:bg-slate-800 p-5 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('dash.sections.recent_messages') }}</h2>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('dash.sections.active_conversations') }}</p>
                            </div>
                            <button @click="navigate(`/dash/mesaje`)" class="flex items-center gap-1 text-xs font-bold text-primary hover:underline">
                                {{ __('dash.view_all') }} <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                            </button>
                        </div>
                        <div class="space-y-2">
                            <template x-if="!stats.recent_conversations?.length">
                                <div class="py-12 text-center text-slate-400 text-sm">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2">chat</span>
                                    <p>{{ __('dash.tables.no_conversations') }}</p>
                                </div>
                            </template>
                            <template x-for="conv in stats.recent_conversations" :key="conv.conversation_id">
                                <div @click="navigate(`/dash/mesaje`)" class="group flex gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer border border-transparent hover:border-slate-100 dark:hover:border-slate-700">
                                    <div class="relative shrink-0">
                                        <template x-if="conv.other_photo">
                                            <img :src="`/storage/` + conv.other_photo" class="w-10 h-10 rounded-xl object-cover border border-slate-100 dark:border-slate-700">
                                        </template>
                                        <template x-if="!conv.other_photo">
                                            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold text-sm" x-text="conv.other_name?.charAt(0)"></div>
                                        </template>
                                        <template x-if="conv.unread > 0">
                                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white rounded-full text-[9px] flex items-center justify-center font-bold" x-text="conv.unread"></span>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-center mb-0.5">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-primary transition-colors truncate" x-text="conv.other_name"></h4>
                                            <span class="text-[10px] text-slate-400 shrink-0 ml-2" x-text="conv.time"></span>
                                        </div>
                                        <p class="text-xs text-slate-500 truncate" x-text="conv.last_message || `{{ __('dash.tables.no_message') }}`"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </section>

                    <!-- Quick Actions - Only for Admins/Managers/Coaches -->
                    <template x-if="['administrator', 'manager', 'antrenor'].includes(user?.role)">
                        <section class="xl:col-span-6 bg-white dark:bg-slate-800 p-5 rounded-[20px] md:rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-50 dark:border-slate-800/60">
                            <div class="mb-4">
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('dash.sections.quick_actions') }}</h2>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('dash.sections.quick_actions_desc') }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <template x-if="user?.role === `administrator`">
                                    <button @click="navigate(`/dash/cluburi`)" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:border-primary hover:bg-primary/5 dark:hover:bg-primary/10 transition-all group text-center">
                                        <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary transition-colors">apartment</span>
                                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400 group-hover:text-primary transition-colors">{{ __('dash.nav.clubs') }}</span>
                                    </button>
                                </template>
                                <button @click="navigate(`/dash/membri`)" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:border-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all group text-center">
                                    <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-emerald-600 transition-colors">group</span>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400 group-hover:text-emerald-600 transition-colors">{{ __('dash.nav.members') }}</span>
                                </button>
                                <button @click="navigate(`/dash/grupe`)" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:border-violet-400 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-all group text-center">
                                    <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-violet-600 transition-colors">diversity_3</span>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400 group-hover:text-violet-600 transition-colors">{{ __('dash.nav.groups') }}</span>
                                </button>
                                <button @click="navigate(`/dash/antrenamente`)" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:border-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-all group text-center">
                                    <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-amber-600 transition-colors">sports</span>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400 group-hover:text-amber-600 transition-colors">{{ __('dash.nav.trainings') }}</span>
                                </button>
                                <button @click="navigate(`/dash/abonamente`)" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:border-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all group text-center">
                                    <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-rose-500 transition-colors">payments</span>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400 group-hover:text-rose-500 transition-colors">{{ __('dash.nav.subscriptions') }}</span>
                                </button>
                                <button @click="navigate(`/dash/mesaje`)" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all group text-center relative">
                                    <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-blue-600 transition-colors">chat</span>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400 group-hover:text-blue-600 transition-colors">{{ __('dash.nav.messages') }}</span>
                                    <template x-if="unreadMessagesCount > 0">
                                        <span class="absolute top-2 right-2 w-5 h-5 bg-red-500 text-white rounded-full text-[9px] flex items-center justify-center font-bold" x-text="unreadMessagesCount"></span>
                                    </template>
                                </button>
                            </div>
                        </section>
                    </template>
                </div>
            </div>