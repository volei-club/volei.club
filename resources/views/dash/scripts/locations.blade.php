Alpine.data('locationManager', () => ({
    locations: [],
    loading: false,
    saving: false,
    showModal: false,
    editingId: null,
    allClubs: [],
    selectedClubId: '',
    restoring: false,
    formData: {
        name: '',
        address: '',
        club_id: ''
    },

    init() {
        // Watch for page changes
        this.$watch('currentPage', (val) => {
            if (val === '/dash/locatii') {
                this.onPageActive();
            }
        });

        // Watch for user data being loaded
        this.$watch('user', (val) => {
            if (val && this.currentPage === '/dash/locatii') {
                this.onPageActive();
            }
        });

        // If page is already active on init
        if (this.currentPage === '/dash/locatii' && this.user) {
            this.onPageActive();
        }
    },

    async onPageActive() {
        this.restoring = true;
        try {
            const params = new URLSearchParams(window.location.hash.slice(1));
            const hashClubId = params.get('club');
            const action = params.get('action');
            const locationId = params.get('id');

            await this.fetchLocations();
            
            if (this.user?.role === 'administrator') {
                await this.fetchClubs();
                if (hashClubId) {
                    this.$nextTick(() => {
                        this.selectedClubId = hashClubId;
                    });
                }
            } else if (this.user?.role === 'manager') {
                this.selectedClubId = this.user.club_id;
                this.formData.club_id = this.user.club_id;
            }

            // Restore modal state
            if (action === 'add') {
                this.$nextTick(() => this.openModal(null));
            } else if (action === 'edit' && locationId) {
                this.$nextTick(() => {
                    const loc = this.locations.find(l => l.id === locationId);
                    if (loc) this.openModal(loc);
                });
            }
        } finally {
            this.restoring = false;
        }
    },

    updateHash() {
        if (this.restoring) return;
        const params = new URLSearchParams();
        if (this.selectedClubId) params.set('club', this.selectedClubId);
        
        if (this.showModal) {
            params.set('action', this.editingId ? 'edit' : 'add');
            if (this.editingId) params.set('id', this.editingId);
        }

        window.location.hash = params.toString();
    },

    async fetchLocations() {
        this.loading = true;
        try {
            const res = await fetch('/api/locations', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                }
            });
            if (res.ok) {
                const data = await res.json();
                this.locations = Array.isArray(data) ? data : (data.data || []);
            }
        } catch (e) {
            console.error("Location fetch error", e);
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

    openModal(loc = null) {
        if (loc) {
            this.editingId = loc.id;
            this.formData = {
                name: loc.name,
                address: loc.address,
                club_id: loc.club_id
            };
        } else {
            this.editingId = null;
            this.formData = {
                name: '',
                address: '',
                club_id: (this.user?.role === 'manager') ? this.user.club_id : ''
            };
        }
        this.showModal = true;
        this.updateHash();
    },

    async saveLocation() {
        if (!this.formData.name || !this.formData.address) {
            window.showToast(`{{ __('admin.locations.required_fields') }}`, 'error');
            return;
        }

        if (!this.user) {
            window.showToast(`{{ __('admin.locations.user_not_found') }}`, 'error');
            return;
        }

        this.saving = true;
        try {
            const method = this.editingId ? 'PUT' : 'POST';
            const url = this.editingId ? `/api/locations/${this.editingId}` : '/api/locations';

            const payload = { ...this.formData };
            if (this.user.role === 'manager') {
                payload.club_id = this.user.club_id;
            }

            if (!payload.club_id && this.user.role === 'administrator') {
                window.showToast(`{{ __('admin.locations.select_club_error') }}`, 'error');
                this.saving = false;
                return;
            }

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
                this.fetchLocations();
                window.showToast(this.editingId ? `{{ __('admin.locations.updated_success') }}` : `{{ __('admin.locations.created_success') }}`);
            } else {
                const data = await res.json();
                window.showToast(data.message || `{{ __('admin.locations.save_error') }}`, 'error');
            }
        } catch (e) {
            window.showToast(`{{ __('admin.error_network') }}`, 'error');
        } finally {
            this.saving = false;
        }
    },

    async deleteLocation(id) {
        if (!confirm(`{{ __('admin.locations.delete_confirm') }}`)) return;

        try {
            const res = await fetch(`/api/locations/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                }
            });

            if (res.ok) {
                this.fetchLocations();
                window.showToast(`{{ __('admin.locations.deleted_success') }}`);
            } else {
                const data = await res.json();
                window.showToast(data.message || `{{ __('admin.locations.delete_error') }}`, 'error');
            }
        } catch (e) {
            window.showToast(`{{ __('admin.error_network') }}`, 'error');
        }
    },

    get filteredLocations() {
        if (!this.selectedClubId) return this.locations;
        return this.locations.filter(l => l.club_id === this.selectedClubId);
    }
}));
