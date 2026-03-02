Alpine.data('squadManager', () => ({
    squads: [],
    availableClubs: [],
    availableModalTeams: [], // Grupele încărcate pentru dropdown-ul din modal de creare
    loading: false,
    saving: false,
    showModal: false,
    error: null,
    form: { id: null, name: '', club_id: '', team_id: '' },
    filters: { club_id: '' },

    init() {
        const syncFromHash = () => {
            let hashClub = '';
            if (window.location.hash && window.location.pathname.startsWith('/dash/echipe')) {
                try {
                    const hp = new URLSearchParams(window.location.hash.substring(1));
                    hashClub = hp.get('club_id') || '';
                } catch(e) {}
            }
            return hashClub;
        };

        const applyFiltersAndFetch = (h) => {
            this.filters.club_id = h;
            this.fetchSquads();
        };

        this.$watch('currentPage', value => {
            if (value === '/dash/echipe') {
                const h = syncFromHash();
                applyFiltersAndFetch(h);
                if (this.user?.role === 'administrator' && this.availableClubs.length === 0) {
                    this.fetchClubs();
                }
            } else if (!value.startsWith('/dash/echipe')) {
                this.filters.club_id = '';
            }
        });

        this.$watch('user', (usr) => {
            if (usr && usr.role === 'administrator' && this.currentPage.startsWith('/dash/echipe')) {
                if (this.availableClubs.length === 0) this.fetchClubs();
            }
        });
        
        this.$watch('availableClubs', (clubs) => {
            if (clubs.length > 0 && this.currentPage.startsWith('/dash/echipe')) {
                const h = syncFromHash();
                applyFiltersAndFetch(h);
            }
        });

        if (this.currentPage.startsWith('/dash/echipe')) {
            const h = syncFromHash();
            applyFiltersAndFetch(h);
            if (this.user?.role === 'administrator') this.fetchClubs();
        }

        window.addEventListener('clubs-updated', () => {
            if (this.user?.role === 'administrator') this.fetchClubs();
        });

        this.$watch('showModal', (val) => {
            if (!val) this.updateHash();
        });
        window.addEventListener('hashchange', () => {
            this.processHashActions();
        });
    },

    processHashActions() {
        if (!this.currentPage.startsWith('/dash/echipe')) return;
        try {
            const hp = new URLSearchParams(window.location.hash.substring(1));
            const action = hp.get('action');
            const id = hp.get('id');
            
            if (action === 'add' && !this.showModal) {
                this.openModal();
            } else if (action === 'edit' && id && !this.showModal) {
                const target = this.squads.find(s => s.id == id);
                if (target) this.openModal(target);
            } else if (action === 'delete' && id) {
                const target = this.squads.find(s => s.id == id);
                if (target) {
                    setTimeout(() => { this.deleteSquad(id); }, 100);
                }
                this.updateHash();
            }
        } catch(e) {}
    },

    updateHash(action = null, targetId = null) {
        const params = new URLSearchParams();
        if (this.filters.club_id) params.append('club_id', this.filters.club_id);
        if (action) params.append('action', action);
        if (targetId) params.append('id', targetId);
        
        const newHash = params.toString() ? '#' + params.toString() : '';
        if (window.location.hash !== newHash) {
            history.replaceState(null, null, newHash || window.location.pathname);
        }
    },

    async fetchModalTeams() {
        // Când selectezi un club in modal, vrem să arătăm doar grupele acelui club
        this.availableModalTeams = [];
        this.form.team_id = ''; // resetare selecție
        if (this.user?.role === 'administrator' && !this.form.club_id) return;
        
        try {
            let url = '/api/teams';
            if (this.form.club_id) {
                url += `?club_id=${this.form.club_id}`;
            }

            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if (res.ok) {
                const payload = await res.json();
                this.availableModalTeams = payload.data;
            }
        } catch(e) {}
    },

    openModal(squad = null) {
        this.error = null;
        if(squad) {
            this.form.id = squad.id;
            this.form.name = squad.name;
            this.form.club_id = squad.team?.club_id || '';
            this.updateHash('edit', squad.id);
            
            // Populăm echipele pentru acel club + selectăm grupa
            if (this.form.club_id || this.user?.role === 'manager') {
                // Dacă e manager, știm sigur că tragem toate echipele din clubul lui implicit (via empty club_id query for teams sau backend filter).
                // Pentru admin, o chemăm explicit.
                this.fetchModalTeams().then(() => {
                    this.form.team_id = squad.team_id;
                });
            } else {
                this.form.team_id = squad.team_id;
            }
        } else {
            this.form.id = null;
            this.form.name = '';
            this.form.club_id = '';
            this.form.team_id = '';
            this.availableModalTeams = [];
            
            // Dacă e manager, încarcă direct grupele lui (fără să trebuiască selecteze club)
            if (this.user?.role === 'manager') {
                this.fetchModalTeams();
            }
            this.updateHash('add');
        }
        this.showModal = true;
    },

    async fetchClubs() {
        try {
            const res = await fetch('/api/clubs', {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if(res.ok) {
                const payload = await res.json();
                this.availableClubs = payload.data;
            }
        } catch(e) {}
    },

    async fetchSquads() {
        this.loading = true;
        try {
            const params = new URLSearchParams();
            if (this.filters.club_id) params.append('club_id', this.filters.club_id);

            const res = await fetch(`/api/squads?${params.toString()}`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if(res.ok) {
                const payload = await res.json();
                this.squads = payload.data;
                this.processHashActions();
            }
        } catch(e) {}
        this.loading = false;
    },

    async saveSquad() {
        this.saving = true;
        this.error = null;
        
        const isEdit = !!this.form.id;
        const url = isEdit ? `/api/squads/${this.form.id}` : '/api/squads';
        const method = isEdit ? 'PUT' : 'POST';
        
        try {
            const res = await fetch(url, {
                method: method,
                headers: { 
                    'Accept': 'application/json', 
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                },
                body: JSON.stringify({ 
                    name: this.form.name,
                    team_id: this.form.team_id
                })
            });
            
            const payload = await res.json();
            
            if(res.ok) {
                this.fetchSquads();
                this.showModal = false;
                window.showToast(isEdit ? 'Echipă actualizată cu succes!' : 'Echipă creată cu succes!');
            } else {
                this.error = payload.message || 'Eroare la salvare.';
                window.showToast(this.error, 'error');
            }
        } catch (e) { 
            this.error = "Eroare de rețea."; 
            window.showToast(this.error, 'error');
        }
        this.saving = false;
    },

    async deleteSquad(id) {
        if(!confirm('Sigur dorești ștergerea acestei echipe? Acțiunea e ireversibilă!')) return;
        
        try {
            const res = await fetch(`/api/squads/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if(res.ok) {
                this.squads = this.squads.filter(s => s.id !== id);
                window.showToast('Echipă ștearsă cu succes!');
            } else {
                const data = await res.json();
                window.showToast(data.message || 'Eroare la ștergere. Posibil echipa are membri asociați.', 'error');
            }
        } catch (e) { window.showToast('A apărut o eroare de rețea.', 'error'); }
    }
}));
