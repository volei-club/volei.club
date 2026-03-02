Alpine.data('profileManager', () => ({
    showModal: false,
    saving: false,
    formData: {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
    },

    openModal() {
        if (!this.user) return;
        this.formData = {
            name: this.user.name,
            email: this.user.email,
            password: '',
            password_confirmation: ''
        };
        this.showModal = true;
    },

    async saveProfile() {
        this.saving = true;
        try {
            const response = await fetch('/api/user/profile', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                },
                body: JSON.stringify(this.formData)
            });

            const data = await response.json();

            if (response.ok) {
                // Update global user object
                this.user = data.data;
                window.showToast('Profil actualizat cu succes!');
                this.showModal = false;
            } else {
                window.showToast(data.message || 'Eroare la actualizarea profilului', 'error');
            }
        } catch (e) {
            console.error(e);
            window.showToast('Eroare de rețea la salvare', 'error');
        } finally {
            this.saving = false;
        }
    }
}));
