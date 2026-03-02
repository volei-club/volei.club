Alpine.data('trainingManager', () => ({
    trainings: [],
    loading: false,
    saving: false,
    showModal: false,
    editingId: null,
    allClubs: [],
    availableLocations: [],
    availableTeams: [],
    availableCoaches: [],
    error: null,
    filters: {
        club_id: '',
        team_id: ''
    },
    formData: {
        club_id: '',
        location_id: '',
        team_id: '',
        coach_id: '',
        day_of_week: 'luni',
        start_time: '18:00',
        end_time: '20:00'
    },

    async init() {
        this.$watch('currentPage', (val) => {
            if (val === '/dash/antrenamente') {
                this.fetchTrainings().then(() => {
                    this.processHashActions();
                });
                if (this.user?.role === 'administrator') {
                    this.fetchClubs();
                } else if (this.user?.club_id) {
                    this.formData.club_id = this.user.club_id;
                    this.onClubChange();
                }
            }
        });

        if (this.currentPage === '/dash/antrenamente') {
            this.fetchTrainings().then(() => {
                this.processHashActions();
            });
            if (this.user?.role === 'administrator') {
                this.fetchClubs();
            } else if (this.user?.club_id) {
                this.formData.club_id = this.user.club_id;
                this.onClubChange();
            }
        }

        this.$watch('formData.club_id', () => {
            this.onClubChange();
        });

        this.$watch('showModal', (val) => {
            if (!val) {
                this.updateHash();
            }
        });

        this.$watch('filters.club_id', () => this.updateHash());
        this.$watch('filters.team_id', () => this.updateHash());
    },

    async fetchTrainings() {
        this.loading = true;
        try {
            let url = '/api/trainings';
            const params = new URLSearchParams();
            
            const clubId = this.filters.club_id || (this.user?.role === 'manager' ? this.user.club_id : '');
            if (clubId) params.append('club_id', clubId);
            if (this.filters.team_id) params.append('team_id', this.filters.team_id);
            
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
            this.availableTeams = [];
            this.availableCoaches = [];
            return;
        }

        // Fetch Locations for club
        fetch(`/api/locations?club_id=${clubId}`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
        }).then(r => r.json()).then(data => this.availableLocations = data);

        // Fetch Teams for club
        fetch(`/api/teams?club_id=${clubId}`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
        }).then(r => r.json()).then(data => this.availableTeams = data.data || data);

        // Fetch Coaches for club (antrenori and managers)
        fetch(`/api/users?club_id=${clubId}&role=antrenor,manager`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
        }).then(r => r.json()).then(data => {
            let coaches = data.data || data;
            // Manual check: include current user if they have the correct role and are in this club
            if (this.user && (this.user.role === 'manager' || this.user.role === 'antrenor')) {
                const currentClubId = this.user.club_id;
                const targetClubId = clubId;
                if (currentClubId == targetClubId) {
                    const alreadyPresent = coaches.some(c => c.id === this.user.id);
                    if (!alreadyPresent) {
                        coaches.unshift(this.user);
                    }
                }
            }
            this.availableCoaches = coaches;
        });
    },

    openModal(t = null) {
        this.error = null;
        if (t) {
            this.editingId = t.id;
            this.formData = {
                club_id: t.club_id,
                location_id: t.location_id,
                team_id: t.team_id,
                coach_id: t.coach_id,
                day_of_week: t.day_of_week,
                start_time: t.start_time.substring(0,5),
                end_time: t.end_time.substring(0,5)
            };
            this.onClubChange();
        } else {
            this.editingId = null;
            this.formData = {
                club_id: this.user?.role === 'manager' ? this.user.club_id : '',
                location_id: '',
                team_id: '',
                coach_id: '',
                day_of_week: 'luni',
                start_time: '18:00',
                end_time: '20:00'
            };
            if (this.user?.role === 'manager' || this.formData.club_id) {
                this.onClubChange();
            }
        }
        this.showModal = true;
        this.updateHash();
    },

    updateHash() {
        const params = new URLSearchParams();
        if (this.filters.club_id) params.set('club_id', this.filters.club_id);
        if (this.filters.team_id) params.set('team_id', this.filters.team_id);
        
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
