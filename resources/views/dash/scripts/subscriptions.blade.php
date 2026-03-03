Alpine.data('subscriptionManager', () => ({
    subscriptions: [],
    availableClubs: [],
    loading: false,
    saving: false,
    showModal: false,
    error: null,
    form: { id: null, name: '', price: '', period: '1_luna', club_id: '' },
    filters: { club_id: '' },

    init() {
        const syncFromHash = () => {
            let hashClub = '';
            if (window.location.hash && window.location.pathname.startsWith('/dash/abonamente')) {
                try {
                    const hp = new URLSearchParams(window.location.hash.substring(1));
                    hashClub = hp.get('club_id') || '';
                } catch(e) {}
            }
            return hashClub;
        };

        const applyFiltersAndFetch = (h) => {
            this.filters.club_id = h;
            this.fetchSubscriptions();
        };

        this.$watch('currentPage', value => {
            if (value === '/dash/abonamente') {
                const h = syncFromHash();
                applyFiltersAndFetch(h);
                if (this.user?.role === 'administrator' && this.availableClubs.length === 0) {
                    this.fetchClubs();
                }
            } else if (!value.startsWith('/dash/abonamente')) {
                this.filters.club_id = '';
            }
        });

        // Avoid errors if user is null initially (before fetch completes)
        this.$watch('user', (usr) => {
            if (usr && usr?.role === 'administrator' && this.currentPage.startsWith('/dash/abonamente')) {
                if (this.availableClubs.length === 0) this.fetchClubs();
            }
        });

        if (this.currentPage.startsWith('/dash/abonamente')) {
            const h = syncFromHash();
            applyFiltersAndFetch(h);
            if (this.user && this.user?.role === 'administrator') this.fetchClubs();
        }

        this.$watch('showModal', (val) => {
            if (!val) this.updateHash();
        });
        window.addEventListener('hashchange', () => {
            this.processHashActions();
        });
    },

    processHashActions() {
        if (!this.currentPage.startsWith('/dash/abonamente')) return;
        try {
            const hp = new URLSearchParams(window.location.hash.substring(1));
            const action = hp.get('action');
            const id = hp.get('id');
            
            if (action === 'add' && !this.showModal) {
                this.openModal();
            } else if (action === 'edit' && id && !this.showModal) {
                const target = this.subscriptions.find(s => s.id == id);
                if (target) this.openModal(target);
            } else if (action === 'delete' && id) {
                const target = this.subscriptions.find(s => s.id == id);
                if (target) {
                    setTimeout(() => { this.deleteSubscription(id); }, 100);
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
            if (!newHash) {
                history.replaceState(null, null, window.location.pathname);
            } else {
                window.location.hash = newHash;
            }
        }
    },

    openModal(sub = null) {
        this.error = null;
        if(sub) {
            this.form.id = sub.id;
            this.form.name = sub.name;
            this.form.price = sub.price;
            this.form.period = sub.period;
            this.form.club_id = sub.club_id;
            this.updateHash('edit', sub.id);
        } else {
            this.form.id = null;
            this.form.name = '';
            this.form.price = '';
            this.form.period = '1_luna';
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

    async fetchSubscriptions() {
        this.loading = true;
        try {
            const params = new URLSearchParams();
            if (this.filters.club_id) params.append('club_id', this.filters.club_id);

            const res = await fetch(`/api/subscriptions?${params.toString()}`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if(res.ok) {
                const payload = await res.json();
                this.subscriptions = payload.data;
                this.processHashActions();
            }
        } catch(e) {}
        this.loading = false;
    },

    async saveSubscription() {
        this.saving = true;
        this.error = null;
        
        const isEdit = !!this.form.id;
        const url = isEdit ? `/api/subscriptions/${this.form.id}` : '/api/subscriptions';
        const method = isEdit ? 'PUT' : 'POST';
        
        try {
            const res = await fetch(url, {
                method: method,
                headers: { 
                    'Accept': 'application/json', 
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                },
                body: JSON.stringify(Object.assign(
                    { name: this.form.name, price: this.form.price, period: this.form.period },
                    this.form.club_id ? { club_id: this.form.club_id } : {}
                ))
            });
            
            const payload = await res.json();
            
            if(res.ok) {
                this.fetchSubscriptions();
                this.showModal = false;
                window.showToast(isEdit ? 'Abonament actualizat cu succes!' : 'Abonament creat cu succes!');
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

    async deleteSubscription(id) {
        if(!confirm('Sigur dorești ștergerea acestui abonament de club?')) return;
        
        try {
            const res = await fetch(`/api/subscriptions/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if(res.ok) {
                this.subscriptions = this.subscriptions.filter(s => s.id !== id);
                window.showToast('Abonament șters cu succes!');
            } else {
                const data = await res.json();
                window.showToast(data.message || 'Eroare la ștergere. Posibil există membri activi asociați.', 'error');
            }
        } catch (e) { window.showToast('A apărut o eroare de rețea.', 'error'); }
    }
}));
