Alpine.data('locationManager', () => ({
    locations: [],
    loading: false,
    saving: false,
    showModal: false,
    editingId: null,
    allClubs: [],
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

    onPageActive() {
        this.fetchLocations();
        if (this.user?.role === 'administrator') {
            this.fetchClubs();
        } else if (this.user?.role === 'manager') {
            this.formData.club_id = this.user.club_id;
        }
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
    },

    async saveLocation() {
        if (!this.formData.name || !this.formData.address) {
            window.showToast("Toate câmpurile sunt obligatorii.", 'error');
            return;
        }

        if (!this.user) {
            window.showToast("Eroare: Utilizator neidentificat.", 'error');
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
                window.showToast("Vă rugăm selectați un club.", 'error');
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
                window.showToast(this.editingId ? 'Locație actualizată!' : 'Locație creată!');
            } else {
                const data = await res.json();
                window.showToast(data.message || "Eroare la salvarea locației.", 'error');
            }
        } catch (e) {
            window.showToast("Eroare de rețea la salvarea locației.", 'error');
        } finally {
            this.saving = false;
        }
    },

    async deleteLocation(id) {
        if (!confirm("Ești sigur că vrei să ștergi această locație?")) return;

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
                window.showToast('Locație ștearsă cu succes!');
            } else {
                const data = await res.json();
                window.showToast(data.message || "Eroare la ștergerea locației.", 'error');
            }
        } catch (e) {
            window.showToast("Eroare de rețea la ștergerea locației.", 'error');
        }
    }
}));
