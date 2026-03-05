Alpine.data('trainingManager', () => ({
    trainings: [],
    loading: false,
    saving: false,
    showModal: false,
    editingId: null,
    allClubs: [],
    availableLocations: [],
    availableSquads: [],
    availableCoaches: [],
    error: null,
    filters: {
        club_id: '',
        squad_id: ''
    },
    formData: {
        club_id: '',
        location_id: '',
        team_id: '',
        squad_id: '',
        coach_id: '',
        day_of_week: 'luni',
        start_time: '18:00',
        end_time: '20:00',
        start_date: '',
        end_date: ''
    },

    async init() {
        // Watch for page changes
        this.$watch('currentPage', (val) => {
            if (val === '/dash/antrenamente') {
                this.onPageActive();
            }
        });

        // Watch for user data being loaded (it's loaded async in dashboard init)
        this.$watch('user', (val) => {
            if (val && this.currentPage === '/dash/antrenamente') {
                this.onPageActive();
            }
        });

        if (this.currentPage === '/dash/antrenamente' && this.user) {
            this.onPageActive();
        }

        this.$watch('formData.club_id', (val) => {
            if (val) this.onClubChange();
        });

        this.$watch('showModal', (val) => {
            if (!val) {
                this.updateHash();
            }
        });

        this.$watch('filters.club_id', () => this.updateHash());
        this.$watch('filters.squad_id', () => this.updateHash());
    },

    onPageActive() {
        this.fetchTrainings().then(() => {
            this.processHashActions();
        });
        if (this.user?.role === 'administrator') {
            this.fetchClubs();
        } else if (this.user?.club_id) {
            this.formData.club_id = this.user.club_id;
            // No need to call onClubChange here, the watch will handle it
        }
    },

    async fetchTrainings() {
        this.loading = true;
        try {
            let url = '/api/trainings';
            const params = new URLSearchParams();
            
            const clubId = this.filters.club_id || (this.user?.role === 'manager' ? this.user.club_id : '');
            if (clubId) params.append('club_id', clubId);
            if (this.filters.squad_id) params.append('squad_id', this.filters.squad_id);
            
            if (params.toString()) url += '?' + params.toString();

            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                }
            });
            if (res.ok) {
                this.trainings = await res.json();
            }
        } catch (e) {
            console.error("Training fetch error", e);
        } finally {
            this.loading = false;
        }
    },

    async fetchClubs() {
        try {
            const res = await fetch('/api/clubs', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                }
            });
            const data = await res.json();
            if (res.ok) {
                this.allClubs = data.data || data;
            }
        } catch (e) {}
    },

    async onClubChange() {
        const clubId = this.formData.club_id || (this.user?.role === 'manager' ? this.user.club_id : '');
        if (!clubId) {
            this.availableLocations = [];
            this.availableSquads = [];
            this.availableCoaches = [];
            return;
        }

        try {
            // Fetch Locations for club
            const locRes = await fetch(`/api/locations?club_id=${clubId}`, {
                headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            this.availableLocations = await locRes.json();

            // Fetch Squads for club
            const squadRes = await fetch(`/api/squads?club_id=${clubId}`, {
                headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            const squadData = await squadRes.json();
            this.availableSquads = squadData.data || squadData;

            // Fetch Coaches for club (antrenori and managers)
            const coachRes = await fetch(`/api/users?club_id=${clubId}&role=antrenor,manager`, {
                headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            const coachData = await coachRes.json();
            let coaches = coachData.data || coachData;
            
            // Manual check: include current user if they have the correct role and are in this club
            if (this.user && (this.user.role === 'manager' || this.user.role === 'antrenor')) {
                const currentClubId = this.user.club_id;
                if (currentClubId == clubId) {
                    const alreadyPresent = coaches.some(c => c.id === this.user.id);
                    if (!alreadyPresent) {
                        coaches.unshift(this.user);
                    }
                }
            }
            this.availableCoaches = coaches;
        } catch (e) {
            console.error("Error in onClubChange", e);
        }
    },

    async openModal(t = null) {
        this.error = null;
        
        // Ensure clubs are fetched for admin if they haven't been yet
        if (this.user?.role === 'administrator' && this.allClubs.length === 0) {
            await this.fetchClubs();
        }

        if (t) {
            this.editingId = t.id;
            // First set the club_id to trigger list loading
            this.formData.club_id = t.club_id;
            
            // Wait for all lists (locations, squads, coaches) to load
            await this.onClubChange();

            // NOW set the dependent values - dropdowns are populated!
            this.formData.location_id = t.location_id;
            this.formData.team_id = t.team_id;
            this.formData.squad_id = t.squad_id;
            this.formData.coach_id = t.coach_id;
            this.formData.day_of_week = t.day_of_week;
            this.formData.start_time = t.start_time.substring(0,5);
            this.formData.end_time = t.end_time.substring(0,5);
            this.formData.start_date = t.start_date || '';
            this.formData.end_date = t.end_date || '';
        } else {
            this.editingId = null;
            this.formData = {
                club_id: this.user?.role === 'manager' ? this.user.club_id : '',
                location_id: '',
                team_id: '',
                squad_id: '',
                coach_id: '',
                day_of_week: 'luni',
                start_time: '18:00',
                end_time: '20:00',
                start_date: '',
                end_date: ''
            };
            if (this.formData.club_id) {
                await this.onClubChange();
            }
        }
        this.showModal = true;
        this.updateHash();
    },

    updateHash() {
        const params = new URLSearchParams();
        if (this.filters.club_id) params.set('club_id', this.filters.club_id);
        if (this.filters.squad_id) params.set('squad_id', this.filters.squad_id);
        
        if (this.showModal) {
            params.set('action', this.editingId ? 'edit' : 'add');
            if (this.editingId) params.set('id', this.editingId);
        }

        const newHash = params.toString() ? '#' + params.toString() : '';
        if (window.location.hash !== newHash) {
            if (!newHash) {
                history.replaceState(null, null, window.location.pathname);
            } else {
                window.location.hash = newHash;
            }
        }
    },

    processHashActions() {
        const hash = window.location.hash.substring(1);
        if (!hash) return;
        
        const params = new URLSearchParams(hash);
        const action = params.get('action');
        const id = params.get('id');

        if (action === 'add') {
            this.openModal();
        } else if (action === 'edit' && id) {
            const training = this.trainings.find(t => t.id == id);
            if (training) {
                this.openModal(training);
            }
        }
    },

    async saveTraining() {
        this.saving = true;
        this.error = null;
        try {
            const method = this.editingId ? 'PUT' : 'POST';
            const url = this.editingId ? `/api/trainings/${this.editingId}` : '/api/trainings';

            const payload = {...this.formData};
            if (this.user?.role === 'manager') payload.club_id = this.user.club_id;

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                this.showModal = false;
                this.updateHash();
                this.fetchTrainings();
                window.showToast(this.editingId ? 'Antrenament actualizat!' : 'Antrenament programat!');
            } else {
                const data = await res.json();
                this.error = data.message || "Eroare la salvarea antrenamentului.";
                window.showToast(this.error, 'error');
            }
        } catch (e) {
            window.showToast("Eroare de rețea la salvarea antrenamentului.", 'error');
        } finally {
            this.saving = false;
        }
    },

    async deleteTraining(id) {
        if (!confirm("Ești sigur că vrei să ștergi acest antrenament?")) return;

        try {
            const res = await fetch(`/api/trainings/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                }
            });

            if (res.ok) {
                this.fetchTrainings();
                window.showToast('Antrenament șters cu succes!');
            } else {
                const data = await res.json();
                window.showToast(data.message || 'Eroare la ștergere.', 'error');
            }
        } catch (e) {
            window.showToast("Eroare de rețea la ștergere.", 'error');
        }
    }
}));
