Alpine.data('systemManager', () => ({
    importing: false,
    selectedFile: null,
    types: [
        { id: 'users', name: 'Membri', icon: 'groups' },
        { id: 'clubs', name: 'Cluburi', icon: 'domain' },
        { id: 'teams', name: 'Grupe', icon: 'diversity_3' },
        { id: 'squads', name: 'Echipe', icon: 'groups_2' },
        { id: 'locations', name: 'Locații', icon: 'location_on' },
        { id: 'subscriptions', name: 'Planuri', icon: 'loyalty' },
        { id: 'trainings', name: 'Antrenamente', icon: 'calendar_month' },
        { id: 'user-subscriptions', name: 'Abonări', icon: 'account_balance_wallet' }
    ],

    init() {
        console.log('System Manager Initialized');
    },

    async exportData(type) {
        try {
            window.showToast(`Se pregătește exportul pentru: ${type}...`);
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
            
            window.showToast('Export finalizat cu succes!');
        } catch (e) {
            console.error(e);
            window.showToast('Eroare la exportul datelor', 'error');
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
                window.showToast(data.message || 'Import finalizat cu succes!');
                this.selectedFile = null;
                // Reset input
                const input = document.querySelector('input[type="file"]');
                if (input) input.value = '';
            } else {
                window.showToast(data.message || 'Eroare la importul datelor', 'error');
            }
        } catch (e) {
            console.error(e);
            window.showToast('Eroare de rețea la import', 'error');
        } finally {
            this.importing = false;
        }
    }
}));
