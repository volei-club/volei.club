Alpine.data('systemManager', () => ({
    importing: false,
    selectedFile: null,
    types: [
        { id: 'users', name: '{{ __('system.types.users') }}', icon: 'groups' },
        { id: 'clubs', name: '{{ __('system.types.clubs') }}', icon: 'domain' },
        { id: 'teams', name: '{{ __('system.types.teams') }}', icon: 'diversity_3' },
        { id: 'squads', name: '{{ __('system.types.squads') }}', icon: 'groups_2' },
        { id: 'locations', name: '{{ __('system.types.locations') }}', icon: 'location_on' },
        { id: 'subscriptions', name: '{{ __('system.types.subscriptions') }}', icon: 'loyalty' },
        { id: 'trainings', name: '{{ __('system.types.trainings') }}', icon: 'calendar_month' },
        { id: 'user-subscriptions', name: '{{ __('system.types.user-subscriptions') }}', icon: 'account_balance_wallet' }
    ],

    init() {
        console.log('System Manager Initialized');
    },

    async exportData(type) {
        try {
            window.showToast(`{{ __('system.export.preparing', ['type' => '']) }}${type}...`);
            const response = await fetch(`/api/export/${type}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                }
            });

            if (!response.ok) throw new Error('Export failed');

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            
            // Extract filename from header if possible
            const contentDisposition = response.headers.get('Content-Disposition');
            let filename = `export_${type}.json`;
            if (contentDisposition) {
                const match = contentDisposition.match(/filename="(.+)"/);
                if (match) filename = match[1];
            }

            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
            
            window.showToast('{{ __('system.export.success') }}');
        } catch (e) {
            console.error(e);
            window.showToast('{{ __('system.export.error') }}', 'error');
        }
    },

    handleFileSelect(e) {
        this.selectedFile = e.target.files[0];
    },

    async importData() {
        if (!this.selectedFile) return;

        this.importing = true;
        const formData = new FormData();
        formData.append('file', this.selectedFile);

        try {
            const response = await fetch('/api/import', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                window.showToast(data.message || '{{ __('system.import.success') }}');
                this.selectedFile = null;
                // Reset input
                const input = document.querySelector('input[type="file"]');
                if (input) input.value = '';
            } else {
                window.showToast(data.message || '{{ __('system.import.error') }}', 'error');
            }
        } catch (e) {
            console.error(e);
            window.showToast('{{ __('system.import.network_error') }}', 'error');
        } finally {
            this.importing = false;
        }
    }
}));
