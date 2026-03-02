Alpine.data('teamManager', () => ({
    teams: [],
    availableClubs: [],
    loading: false,
    saving: false,
    showModal: false,
    error: null,
    form: { id: null, name: '', club_id: '' },
    filters: { club_id: '' },

    init() {
        const syncFromHash = () => {
            let hashClub = '';
            if (window.location.hash && window.location.pathname.startsWith('/dash/grupe')) {
                try {
                    const hp = new URLSearchParams(window.location.hash.substring(1));
                    hashClub = hp.get('club_id') || '';
                } catch(e) {}
            }
            return hashClub;
        };

        const applyFiltersAndFetch = (h) => {
            this.filters.club_id = h;
            
            setTimeout(() => {
                const cSelect = document.getElementById('teamFilterClub');
                if (cSelect) cSelect.value = h;
                this.fetchTeams();
            }, 50);
        };

        this.$watch('currentPage', value => {
            if (value === '/dash/grupe') {
                const h = syncFromHash();
                applyFiltersAndFetch(h);
                if (this.user?.role === 'administrator' && this.availableClubs.length === 0) {
                    this.fetchClubs();
                }
            } else if (!value.startsWith('/dash/grupe')) {
                this.filters.club_id = '';
            }
        });

        this.$watch('user', (usr) => {
            if (usr && usr.role === 'administrator' && this.currentPage.startsWith('/dash/grupe')) {
                if (this.availableClubs.length === 0) this.fetchClubs();
            }
        });

        this.$watch('availableClubs', (clubs) => {
            if (clubs.length > 0 && this.currentPage.startsWith('/dash/grupe')) {
                const h = syncFromHash();
                applyFiltersAndFetch(h);
            }
        });

        if (this.currentPage.startsWith('/dash/grupe')) {
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
        if (!this.currentPage.startsWith('/dash/grupe')) return;
        try {
            const hp = new URLSearchParams(window.location.hash.substring(1));
            const action = hp.get('action');
            const id = hp.get('id');
            
            if (action === 'add' && !this.showModal) {
                this.openModal();
            } else if (action === 'edit' && id && !this.showModal) {
                const target = this.teams.find(t => t.id == id);
                if (target) this.openModal(target);
            } else if (action === 'delete' && id) {
                const target = this.teams.find(t => t.id == id);
                if (target) {
                    setTimeout(() => { this.deleteTeam(id); }, 100);
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

    openModal(team = null) {
        this.error = null;
        if(team) {
            this.form.id = team.id;
            this.form.name = team.name;
            this.form.club_id = team.club_id;
            this.updateHash('edit', team.id);
        } else {
            this.form.id = null;
            this.form.name = '';
            this.form.club_id = '';
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

    async fetchTeams() {
        this.loading = true;
        try {
            const params = new URLSearchParams();
            if (this.filters.club_id) params.append('club_id', this.filters.club_id);

            const res = await fetch(`/api/teams?${params.toString()}`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if(res.ok) {
                const payload = await res.json();
                this.teams = payload.data;
                this.processHashActions();
            }
        } catch (e) { console.error(e); }
        this.loading = false;
    },

    async saveTeam() {
        this.saving = true;
        this.error = null;
        
        const isEdit = !!this.form.id;
        const url = isEdit ? `/api/teams/${this.form.id}` : '/api/teams';
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
                    club_id: this.form.club_id || null
                })
            });
            
            const payload = await res.json();
            
            if(res.ok) {
                this.fetchTeams();
                this.showModal = false;
                window.showToast(isEdit ? 'Grupă actualizată cu succes!' : 'Grupă creată cu succes!');
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

    async deleteTeam(id) {
        if(!confirm('Sigur dorești ștergerea acestei grupe? Acțiunea e ireversibilă!')) return;
        
        try {
            const res = await fetch(`/api/teams/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if(res.ok) {
                this.teams = this.teams.filter(t => t.id !== id);
                window.showToast('Grupă ștearsă cu succes!');
            } else {
                const data = await res.json();
                window.showToast(data.message || 'Eroare la ștergere. Posibil grupa are membri asociați.', 'error');
            }
        } catch (e) { window.showToast('A apărut o eroare de rețea.', 'error'); }
    }
}));
