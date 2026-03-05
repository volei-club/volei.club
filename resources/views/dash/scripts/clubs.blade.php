Alpine.data('clubManager', () => ({
    clubs: [],
    loading: false,
    saving: false,
    showModal: false,
    error: null,
    form: { id: null, name: '' },

    init() {
        if (this.currentPage.startsWith('/dash/cluburi')) {
            this.fetchClubs();
        }
        this.$watch('currentPage', value => {
            if (value === '/dash/cluburi' && this.clubs.length === 0) {
                this.fetchClubs();
            }
        });
        this.$watch('showModal', (val) => {
            if (!val) this.updateHash();
        });
        window.addEventListener('hashchange', () => {
            this.processHashActions();
        });
    },

    processHashActions() {
        if (!this.currentPage.startsWith('/dash/cluburi')) return;
        try {
            const hp = new URLSearchParams(window.location.hash.substring(1));
            const action = hp.get('action');
            const id = hp.get('id');
            
            if (action === 'add' && !this.showModal) {
                this.openModal();
            } else if (action === 'edit' && id && !this.showModal) {
                const target = this.clubs.find(c => c.id == id);
                if (target) this.openModal(target);
            } else if (action === 'delete' && id) {
                const target = this.clubs.find(c => c.id == id);
                if (target) {
                    setTimeout(() => { this.deleteClub(id); }, 100);
                }
                this.updateHash();
            }
        } catch(e) {}
    },

    updateHash(action = null, targetId = null) {
        const params = new URLSearchParams();
        if (action) params.append('action', action);
        if (targetId) params.append('id', targetId);
        
        const newHash = params.toString() ? '#' + params.toString() : '';
        if (window.location.hash !== newHash) {
            history.replaceState(null, null, newHash || window.location.pathname);
        }
    },

    openModal(club = null) {
        this.error = null;
        if(club) {
            this.form.id = club.id;
            this.form.name = club.name;
            this.updateHash('edit', club.id);
        } else {
            this.form.id = null;
            this.form.name = '';
            this.updateHash('add');
        }
        this.showModal = true;
    },

    async fetchClubs() {
        this.loading = true;
        try {
            const res = await fetch('/api/clubs', {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if(res.ok) {
                const payload = await res.json();
                this.clubs = payload.data;
                this.processHashActions();
            }
        } catch (e) { console.error(e); }
        this.loading = false;
    },

    async saveClub() {
        this.saving = true;
        this.error = null;
        
        const isEdit = !!this.form.id;
        const url = isEdit ? `/api/clubs/${this.form.id}` : '/api/clubs';
        const method = isEdit ? 'PUT' : 'POST';
        
        try {
            const res = await fetch(url, {
                method: method,
                headers: { 
                    'Accept': 'application/json', 
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                },
                body: JSON.stringify({ name: this.form.name })
            });
            
            const payload = await res.json();
            
            if(res.ok) {
                if (isEdit) {
                    const idx = this.clubs.findIndex(c => c.id === this.form.id);
                    if (idx !== -1) this.clubs[idx].name = payload.data.name;
                    window.showToast(`{{ __('admin.clubs.updated_success') }}`);
                } else {
                    this.clubs.unshift(payload.data);
                    window.showToast(`{{ __('admin.clubs.created_success') }}`);
                }
                window.dispatchEvent(new CustomEvent('clubs-updated'));
                this.showModal = false;
            } else {
                this.error = payload.message || `{{ __('admin.clubs.save_error') }}`;
                window.showToast(this.error, 'error');
            }
        } catch (e) { 
            this.error = `{{ __('admin.error_network') }}`; 
            window.showToast(this.error, 'error');
        }
        this.saving = false;
    },

    async deleteClub(id) {
        if(!confirm(`{{ __('admin.clubs.delete_confirm') }}`)) return;
        
        try {
            const res = await fetch(`/api/clubs/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if(res.ok) {
                this.clubs = this.clubs.filter(c => c.id !== id);
                window.showToast(`{{ __('admin.clubs.deleted_success') }}`);
            } else {
                const data = await res.json();
                window.showToast(data.message || '{{ __('admin.clubs.delete_error') }}', 'error');
            }
        } catch (e) { window.showToast(`{{ __('admin.error_network') }}`, 'error'); }
    }
}));
