Alpine.store('gameModal', {
    show: false,
    editingId: null,
    loadingMembers: false,
    saving: false,
    members: [],
    availableSquads: [],
    formData: {
        opponent_name: '',
        location: '',
        match_date: '',
        squad_id: '',
        notes: '',
        starters: [],
        substitutes: [],
        set1_home: '', set1_away: '',
        set2_home: '', set2_away: '',
        set3_home: '', set3_away: '',
        set4_home: '', set4_away: '',
        set5_home: '', set5_away: ''
    },

    async open(game = null) {
        // Fetch squads if not yet loaded
        if (this.availableSquads.length === 0) {
            try {
                const res = await axios.get('/api/squads');
                this.availableSquads = res.data.data;
            } catch (e) {}
        }

        if (game) {
            // Extract raw_game if it's wrapped (from calendar)
            const g = game.raw_game || game;
            
            this.editingId = g.id;
            this.formData = {
                opponent_name: g.opponent_name,
                location: g.location,
                match_date: g.match_date ? g.match_date.replace(' ', 'T').substring(0, 16) : '',
                squad_id: g.squad_id,
                notes: g.notes || '',
                starters: g.players?.filter(p => p.pivot.type === 'titular').map(p => p.id) || [],
                substitutes: g.players?.filter(p => p.pivot.type === 'rezerva').map(p => p.id) || [],
                set1_home: g.set1_home, set1_away: g.set1_away,
                set2_home: g.set2_home, set2_away: g.set2_away,
                set3_home: g.set3_home, set3_away: g.set3_away,
                set4_home: g.set4_home, set4_away: g.set4_away,
                set5_home: g.set5_home, set5_away: g.set5_away
            };
            this.fetchMembers(g.squad_id);
        } else {
            this.editingId = null;
            this.formData = {
                opponent_name: '',
                location: '',
                match_date: '',
                squad_id: '',
                notes: '',
                starters: [],
                substitutes: [],
                set1_home: '', set1_away: '',
                set2_home: '', set2_away: '',
                set3_home: '', set3_away: '',
                set4_home: '', set4_away: '',
                set5_home: '', set5_away: ''
            };
            this.members = [];
        }
        this.show = true;
    },

    async fetchMembers(squadId) {
        if (!squadId) {
            this.members = [];
            return;
        }
        this.loadingMembers = true;
        try {
            const res = await axios.get(`/api/squads/${squadId}`);
            this.members = res.data.users || [];
        } catch (e) {
            console.error(e);
        } finally {
            this.loadingMembers = false;
        }
    },

    togglePlayer(userId, type) {
        if (type === 'titular') {
            if (this.formData.starters.includes(userId)) {
                this.formData.starters = this.formData.starters.filter(id => id !== userId);
            } else {
                this.formData.starters.push(userId);
                this.formData.substitutes = this.formData.substitutes.filter(id => id !== userId);
            }
        } else {
            if (this.formData.substitutes.includes(userId)) {
                this.formData.substitutes = this.formData.substitutes.filter(id => id !== userId);
            } else {
                this.formData.substitutes.push(userId);
                this.formData.starters = this.formData.starters.filter(id => id !== userId);
            }
        }
    },

    async save() {
        this.saving = true;
        try {
            const url = this.editingId ? `/api/games/${this.editingId}` : '/api/games';
            const method = this.editingId ? 'put' : 'post';
            await axios[method](url, this.formData);
            window.showToast(this.editingId ? 'Meci actualizat' : 'Meci creat');
            this.show = false;
            window.dispatchEvent(new CustomEvent('game-saved'));
            window.dispatchEvent(new CustomEvent('refresh-calendar'));
        } catch (e) {
             window.showToast('Eroare la salvare', 'error');
        } finally {
            this.saving = false;
        }
    }
});
