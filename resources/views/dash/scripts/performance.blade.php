Alpine.data('performanceManager', () => ({
    logs: [],
    availableAthletes: [],
    athleteSearch: '',
    selectedAthleteId: null,

    activeMetric: 'detenta', // 'detenta', 'viteza', 'greutate'
    activeTooltip: null,

    clubs: [],
    teams: [],
    squads: [],
    selectedClubId: '',
    selectedTeamId: '',
    selectedSquadId: '',

    pendingAthleteId: null,
    loading: false,
    saving: false,
    restoring: false,
    showModal: false,
    formData: {
        user_id: '',
        log_date: new Date().toISOString().slice(0, 10),
        weight: '',
        vertical_jump: '',
        serve_speed: '',
        reception_rating: '',
        attack_rating: '',
        block_rating: '',
        notes: ''
    },

    init() {
        this.$watch('currentPage', (val) => {
            if (val === '/dash/performanta') {
                this.onPageActive();
            }
        });

        this.$watch('user', (val) => {
            if (val && this.currentPage === '/dash/performanta') {
                this.onPageActive();
            }
        });

        if (this.currentPage === '/dash/performanta' && this.user) {
            this.onPageActive();
        }
    },

    async onPageActive() {
        this.restoring = true;
        try {
            const params = new URLSearchParams(window.location.hash.slice(1));
            const hashAthleteId = params.get('athlete');
            const hashClubId = params.get('club');
            const hashTeamId = params.get('team');
            const hashSquadId = params.get('squad');

            if (['administrator', 'manager', 'antrenor'].includes(this.user?.role)) {
                await this.fetchClubs();
                
                if (hashClubId) {
                    this.selectedClubId = hashClubId;
                } else if (this.user.role !== 'administrator' && this.user.club_id) {
                    this.selectedClubId = this.user.club_id;
                }

                if (this.selectedClubId) {
                    await this.fetchTeams();
                    if (hashTeamId) {
                        this.selectedTeamId = hashTeamId;
                        await this.fetchSquads();
                        if (hashSquadId) {
                            this.selectedSquadId = hashSquadId;
                        }
                    }
                }
                
                if (hashAthleteId) this.pendingAthleteId = hashAthleteId;
                await this.fetchAthletes();
            } else if (this.user?.role === 'sportiv') {
                this.selectedAthleteId = this.user.id;
                this.fetchHistory();
            } else if (this.user?.role === 'parinte') {
                await this.fetchChildren();
            }
        } finally {
            this.restoring = false;
        }
    },

    updateHash() {
        if (this.restoring) return;
        const params = new URLSearchParams();
        if (this.selectedClubId) params.set('club', this.selectedClubId);
        if (this.selectedTeamId) params.set('team', this.selectedTeamId);
        if (this.selectedSquadId) params.set('squad', this.selectedSquadId);
        if (this.selectedAthleteId) params.set('athlete', this.selectedAthleteId);
        window.location.hash = params.toString();
    },

    async fetchClubs() {
        try {
            const res = await fetch('/api/clubs', {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            const data = await res.json();
            if (res.ok) {
                this.clubs = data.data || [];
            }
        } catch (e) {}
    },

    async fetchTeams() {
        if (!this.selectedClubId) {
            this.teams = [];
            return;
        }
        try {
            const res = await fetch(`/api/teams?club_id=${this.selectedClubId}&per_page=100`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            const data = await res.json();
            if (res.ok) {
                this.teams = data.data || [];
            }
        } catch (e) {}
    },

    async fetchSquads() {
        if (!this.selectedTeamId) {
            this.squads = [];
            return;
        }
        try {
            const res = await fetch(`/api/squads?team_id=${this.selectedTeamId}&per_page=100`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            const data = await res.json();
            if (res.ok) {
                this.squads = data.data || [];
            }
        } catch (e) {}
    },

    async fetchAthletes() {
        let url = '/api/users?role=sportiv&per_page=500';
        if (this.selectedClubId) url += `&club_id=${this.selectedClubId}`;
        if (this.selectedTeamId) url += `&team_id=${this.selectedTeamId}`;
        if (this.selectedSquadId) url += `&squad_id=${this.selectedSquadId}`;

        try {
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            const data = await res.json();
            if (res.ok) {
                this.availableAthletes = data.data || [];
                
                setTimeout(() => {
                    const targetId = this.pendingAthleteId || this.selectedAthleteId;
                    const exists = this.availableAthletes.find(a => a.id === targetId);
                    
                    if (exists) {
                        this.selectedAthleteId = exists.id;
                        this.fetchHistory();
                        this.updateHash();
                        this.pendingAthleteId = null;
                    } else if (this.availableAthletes.length > 0 && !targetId) {
                        this.selectAthlete(this.availableAthletes[0]);
                    } else if (targetId) {
                        // If it doesn't exist yet, keep it as pending
                        this.selectedAthleteId = targetId;
                        this.fetchHistory();
                    }
                }, 100);
            }
        } catch (e) {}
    },

    async fetchChildren() {
        try {
            const res = await fetch('/api/users?per_page=100', {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            const data = await res.json();
            if (res.ok) {
                const childIds = this.user.children_ids || [];
                this.availableAthletes = (data.data || []).filter(u => u.role === 'sportiv');
                if (this.availableAthletes.length > 0) {
                    this.selectAthlete(this.availableAthletes[0]);
                }
            }
        } catch (e) {}
    },

    async onClubChange(id) {
        this.selectedClubId = id;
        this.selectedTeamId = '';
        this.selectedSquadId = '';
        this.selectedAthleteId = null;
        this.teams = [];
        this.squads = [];
        this.updateHash();
        await this.fetchTeams();
        await this.fetchAthletes();
    },

    async onTeamChange(id) {
        this.selectedTeamId = id;
        this.selectedSquadId = '';
        this.selectedAthleteId = null;
        this.squads = [];
        this.updateHash();
        await this.fetchSquads();
        await this.fetchAthletes();
    },

    async onSquadChange(id) {
        this.selectedSquadId = id;
        this.selectedAthleteId = null;
        this.updateHash();
        await this.fetchAthletes();
    },

    selectAthleteById(id) {
        const athlete = this.availableAthletes.find(a => a.id === id);
        if (athlete) this.selectAthlete(athlete);
    },

    selectAthlete(athlete) {
        if (!athlete) return;
        this.selectedAthleteId = athlete.id;
        this.updateHash();
        this.fetchHistory();
    },

    onModalAthleteChange(id) {
        const athlete = this.availableAthletes.find(a => a.id === id);
        if (athlete) {
            this.selectAthlete(athlete);
        }
    },

    get filteredAthletes() {
        if (!this.athleteSearch) return this.availableAthletes;
        const q = this.athleteSearch.toLowerCase();
        return this.availableAthletes.filter(a => a.name.toLowerCase().includes(q));
    },

    async fetchHistory() {
        if (!this.selectedAthleteId) return;
        this.loading = true;
        try {
            const res = await fetch(`/api/performance/${this.selectedAthleteId}`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            const data = await res.json();
            if (res.ok) {
                this.logs = data.data || [];
            }
        } catch (e) {}
        this.loading = false;
    },

    get latestEntry() {
        return this.logs[0] || null;
    },

    get activeMetricLabel() {
        if (this.activeMetric === 'viteza') return 'Viteză Serviciu';
        if (this.activeMetric === 'greutate') return 'Greutate';
        return 'Detentă';
    },

    get activeMetricUnit() {
        if (this.activeMetric === 'viteza') return 'km/h';
        if (this.activeMetric === 'greutate') return 'kg';
        return 'cm';
    },

    setMetric(m) {
        this.activeMetric = m;
    },

    canAddEntry() {
        return ['administrator', 'manager', 'antrenor'].includes(this.user?.role);
    },

    canDeleteEntry(log) {
        if (this.user?.role === 'administrator') return true;
        if (this.user?.role === 'manager') return true;
        if (this.user?.role === 'antrenor' && log.coach_id === this.user.id) return true;
        return false;
    },

    formatDate(date) {
        return new Date(date).toLocaleDateString('ro-RO', { day: '2-digit', month: '2-digit', year: 'numeric' });
    },

    openModal() {
        this.formData = {
            user_id: this.selectedAthleteId,
            log_date: new Date().toISOString().slice(0, 10),
            weight: this.latestEntry?.weight || '',
            vertical_jump: '',
            serve_speed: '',
            reception_rating: '',
            attack_rating: '',
            block_rating: '',
            notes: ''
        };
        this.showModal = true;
    },

    async saveEntry() {
        if (!this.formData.user_id) return;
        this.saving = true;
        try {
            const res = await fetch('/api/performance', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                },
                body: JSON.stringify(this.formData)
            });
            if (res.ok) {
                window.showToast('Progres salvat cu succes!');
                this.showModal = false;
                this.fetchHistory();
            } else {
                const data = await res.json();
                window.showToast(data.message || 'Eroare la salvare', 'error');
            }
        } catch (e) {
            window.showToast('Eroare de rețea', 'error');
        }
        this.saving = false;
    },

    async deleteEntry(id) {
        if (!confirm('Ești sigur că vrei să ștergi această înregistrare?')) return;
        try {
            const res = await fetch(`/api/performance/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if (res.ok) {
                window.showToast('Înregistrare ștearsă.');
                this.fetchHistory();
            }
        } catch (e) {}
    },

    // Charting Logic
    get chartMinMax() {
        if (this.logs.length < 2) return { min: 0, max: 10, range: 10, chartMin: -2, chartMax: 12 };
        const data = [...this.logs];
        
        let field = 'vertical_jump';
        if (this.activeMetric === 'viteza') field = 'serve_speed';
        if (this.activeMetric === 'greutate') field = 'weight';

        const vals = data.map(l => l[field] || 0);
        const maxVal = Math.max(...vals, 1);
        const minVal = Math.min(...vals);
        
        const range = maxVal - minVal;
        const padding = range * 0.2 || 10;
        const chartMin = minVal - padding;
        const chartMax = maxVal + padding;
        const chartRange = chartMax - chartMin;

        return { min: minVal, max: maxVal, range: chartRange, chartMin, chartMax };
    },

    get chartMaxVal() {
        return Math.round(this.chartMinMax.chartMax);
    },

    get chartMinVal() {
        return Math.round(this.chartMinMax.chartMin);
    },

    get chartPoints() {
        const { chartMin, chartMax, range } = this.chartMinMax;
        if (this.logs.length < 2) return [];
        const data = [...this.logs].reverse(); // oldest first
        
        let field = 'vertical_jump';
        if (this.activeMetric === 'viteza') field = 'serve_speed';
        if (this.activeMetric === 'greutate') field = 'weight';

        return data.map((l, i) => ({
            x: (i / (data.length - 1)) * 900 + 50,
            y: 280 - (((l[field] || 0) - chartMin) / range) * 250,
            val: (l[field] || 0),
            unit: this.activeMetricUnit,
            label: this.formatDate(l.log_date).slice(0, 5), // dd.mm
            raw: l
        }));
    },

    get chartDataPath() {
        const points = this.chartPoints;
        if (points.length < 2) return '';
        return points.reduce((acc, p, i) => i === 0 ? `M ${p.x} ${p.y}` : `${acc} L ${p.x} ${p.y}`, '');
    }
}));
