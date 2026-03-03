Alpine.data('gameManager', () => ({
    games: [],
    loading: false,
    allClubs: [],
    availableSquads: [],
    filters: {
        club_id: '',
        squad_id: ''
    },

    async init() {
        this.$watch('currentPage', (val) => {
            if (val === '/dash/meciuri') {
                this.fetchGames();
                this.loadInitialData();
            }
        });

        if (this.currentPage === '/dash/meciuri') {
            this.fetchGames();
            this.loadInitialData();
        }

        // Listen for global game modal events
        window.addEventListener('game-saved', () => {
            if (this.currentPage === '/dash/meciuri') {
                this.fetchGames();
            }
        });
    },

    async loadInitialData() {
        if (this.user?.role === 'administrator') {
            try {
                const res = await axios.get('/api/clubs');
                this.allClubs = res.data.data;
            } catch (e) {}
        }
        this.fetchAvailableSquads();
    },

    async fetchAvailableSquads() {
        try {
            const params = {};
            if (this.filters.club_id) params.club_id = this.filters.club_id;
            const res = await axios.get('/api/squads', { params });
            this.availableSquads = res.data.data;
        } catch (e) {}
    },

    async fetchGames() {
        this.loading = true;
        try {
            const params = { ...this.filters };
            const res = await axios.get('/api/games', { params });
            this.games = res.data;
        } catch (e) {
            window.showToast('Eroare la încărcarea meciurilor', 'error');
        } finally {
            this.loading = false;
        }
    },

    canAddGame() {
        return this.canModifyMatches();
    },

    canModifyMatches() {
        return ['administrator', 'manager', 'antrenor'].includes(this.user?.role);
    },

    openGameModal(game = null) {
        Alpine.store('gameModal').open(game);
    },

    deleteGame(id) {
        if (!confirm('Ești sigur că vrei să ștergi acest meci?')) return;
        axios.delete(`/api/games/${id}`).then(() => {
            window.showToast('Meci șters');
            this.fetchGames();
            window.dispatchEvent(new CustomEvent('refresh-calendar'));
        });
    },

    formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return d.toLocaleDateString('ro-RO', { weekday: 'short', day: 'numeric', month: 'short' });
    },

    formatTime(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return d.toLocaleTimeString('ro-RO', { hour: '2-digit', minute: '2-digit' });
    },

    getFinalScore(game) {
        let homeSets = 0;
        let awaySets = 0;
        for (let i = 1; i <= 5; i++) {
            const h = parseInt(game['set' + i + '_home']);
            const a = parseInt(game['set' + i + '_away']);
            if (!isNaN(h) && !isNaN(a)) {
                if (h > a) homeSets++;
                else if (a > h) awaySets++;
            }
        }
        if (homeSets === 0 && awaySets === 0) return null;
        return `${homeSets}-${awaySets}`;
    }
}));
