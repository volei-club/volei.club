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

    async init() {
        this.$watch('currentPage', (val) => {
            if (val === '/dash/locatii') {
                this.fetchLocations();
                if (this.user?.role === 'administrator') {
                    this.fetchClubs();
                }
            }
        });

        if (this.currentPage === '/dash/locatii') {
            this.fetchLocations();
            if (this.user?.role === 'administrator') {
                this.fetchClubs();
            }
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
                this.locations = await res.json();
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
                club_id: this.user?.role === 'manager' ? this.user.club_id : ''
            };
        }
        this.showModal = true;
    },

    async saveLocation() {
        this.saving = true;
        try {
            const method = this.editingId ? 'PUT' : 'POST';
            const url = this.editingId ? `/api/locations/${this.editingId}` : '/api/locations';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                },
                body: JSON.stringify(this.formData)
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
