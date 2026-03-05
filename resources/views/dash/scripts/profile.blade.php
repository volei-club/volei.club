Alpine.data('profileManager', () => ({
    showModal: false,
    saving: false,
    photoPreview: null,
    formData: {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
    },

    openModal() {
        if (!this.user) return;
        this.photoPreview = null;
        this.formData = {
            name: this.user.name,
            email: this.user.email,
            password: '',
            password_confirmation: ''
        };
        this.showModal = true;
    },

    handlePhotoSelect(e) {
        const file = e.target.files[0];
        if (file) {
            window.dispatchEvent(new CustomEvent('open-cropper', {
                detail: {
                    file: file,
                    callback: (croppedFile, previewUrl) => {
                        this.formData.photo = croppedFile;
                        this.photoPreview = previewUrl;
                    }
                }
            }));
        }
    },

    async saveProfile() {
        this.saving = true;
        
        const data = new FormData();
        data.append('_method', 'PUT'); // Laravel spoofing for PUT with FormData
        data.append('name', this.formData.name);
        data.append('email', this.formData.email);
        if (this.formData.password) {
            data.append('password', this.formData.password);
            data.append('password_confirmation', this.formData.password_confirmation);
        }
        if (this.formData.photo) {
            // If it's a blob from the cropper, give it a filename
            if (this.formData.photo instanceof Blob && !(this.formData.photo instanceof File)) {
                data.append('photo', this.formData.photo, 'avatar.jpg');
            } else {
                data.append('photo', this.formData.photo);
            }
        }

        try {
            const response = await fetch('/api/user/profile', {
                method: 'POST', // Use POST with _method spoofing for files
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                },
                body: data
            });

            const result = await response.json();

            if (response.ok) {
                // Update global user object
                this.user = result.data;
                window.showToast('{{ __('profile.messages.success_saved') }}');
                this.showModal = false;
            } else {
                window.showToast(result.message || '{{ __('profile.messages.error_save') }}', 'error');
            }
        } catch (e) {
            console.error(e);
            window.showToast('{{ __('profile.messages.network_error') }}', 'error');
        } finally {
            this.saving = false;
        }
    }
}));
