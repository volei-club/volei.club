    <!-- Alpine App Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            // Global Helper for Toasts
            window.showToast = (message, type = 'success') => {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message, type } }));
            };

            // ------- Gestiune Cluburi -------
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
                                window.showToast('Club actualizat cu succes!');
                            } else {
                                this.clubs.unshift(payload.data);
                                window.showToast('Club creat cu succes!');
                            }
                            window.dispatchEvent(new CustomEvent('clubs-updated'));
                            this.showModal = false;
                        } else {
                            this.error = payload.message || 'Eroare la salvare.';
                            window.showToast(this.error, 'error');
                        }
                    } catch (e) { 
                        this.error = "Eroare de rețea."; 
                        window.showToast(this.error, 'error');
                    }
                    this.saving = false;
                },

                async deleteClub(id) {
                    if(!confirm('Sigur dorești ștergerea acestui club? Acțiunea e ireversibilă!')) return;
                    
                    try {
                        const res = await fetch(`/api/clubs/${id}`, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            this.clubs = this.clubs.filter(c => c.id !== id);
                            window.showToast('Club șters cu succes!');
                        } else {
                            const data = await res.json();
                            window.showToast(data.message || 'Eroare la ștergere.', 'error');
                        }
                    } catch (e) { window.showToast('A apărut o eroare de rețea.', 'error'); }
                }
            }));

            // ------- Gestiune Membru -------
            Alpine.data('userManager', () => ({
                users: [],
                availableClubs: [],
                availableTeams: [],
                availableFilterTeams: [],
                availableFilterSquads: [],
                availableSquads: [],
                loading: false,
                loadingTeams: false,
                loadingSquads: false,
                saving: false,
                showModal: false,
                error: null,
                form: { id: null, name: '', email: '', role: '', club_id: '', password: '', is_active: true, team_ids: [], squad_ids: [] },
                
                // Subscription properties
                showSubscriptionModal: false,
                availableSubscriptions: [],
                subscriptionForm: { user_id: null, user_name: '', current_subscription: null, subscription_id: '' },
                subscriptionError: null,
                savingSubscription: false,
                showSubscriptionHistoryModal: false,
                historyUser: null,
                statusLabels: {
                    'active_paid': 'Plătit',
                    'active_pending': 'Așteaptă Plată',
                    'expired': 'Expirat',
                    'cancelled': 'Anulat',
                    'inactive_expired': 'Expirat',
                    // Fallbacks for uppercase if they ever appear
                    'ACTIVE_PAID': 'Plătit',
                    'ACTIVE_PENDING': 'Așteaptă Plată',
                    'CANCELLED': 'Anulat'
                },

                filters: { role: '', club_id: '', team_id: '', squad_id: '' },

                init() {
                    const syncFromHash = () => {
                        let hashRole = '';
                        let hashClub = '';
                        let hashTeam = '';
                        let hashSquad = '';
                        if (window.location.hash && window.location.pathname.startsWith('/dash/membri')) {
                            try {
                                const hp = new URLSearchParams(window.location.hash.substring(1));
                                hashRole = hp.get('role') || '';
                                hashClub = hp.get('club_id') || '';
                                hashTeam = hp.get('team_id') || '';
                                hashSquad = hp.get('squad_id') || '';
                            } catch(e) {}
                        }
                        return { role: hashRole, club: hashClub, team: hashTeam, squad: hashSquad };
                    };

                    const applyFiltersAndFetch = async (h) => {
                        this.filters.role = h.role;
                        this.filters.club_id = h.club;
                        this.filters.team_id = h.team;
                        this.filters.squad_id = h.squad;
                        
                        if (h.club || this.user?.role === 'manager') {
                             await this.fetchFilterTeams(h.club || this.user?.club_id);
                        }
                        if (h.team) {
                             await this.fetchFilterSquads(h.team);
                        }

                        // Break Alpine cache by forcefully mutating the native DOM element.
                        setTimeout(() => {
                            const rSelect = document.getElementById('userFilterRole');
                            const cSelect = document.getElementById('userFilterClub');
                            const tSelect = document.getElementById('userFilterTeam');
                            const sSelect = document.getElementById('userFilterSquad');
                            if (rSelect) rSelect.value = h.role;
                            if (cSelect) cSelect.value = h.club;
                            if (tSelect) tSelect.value = h.team;
                            if (sSelect) sSelect.value = h.squad;
                            this.fetchUsers();
                        }, 50);
                    };

                    this.$watch('currentPage', value => {
                        if (value === '/dash/membri') {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                            if (this.user?.role === 'administrator' && this.availableClubs.length === 0) {
                                this.fetchDependentData();
                            }
                        } else if (!value.startsWith('/dash/membri')) {
                            this.filters.role = '';
                            this.filters.club_id = '';
                            this.filters.team_id = '';
                        }
                    });

                    this.$watch('user', (usr) => {
                        if (usr && this.currentPage.startsWith('/dash/membri')) {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                            if (usr.role === 'administrator' && this.availableClubs.length === 0) {
                                this.fetchDependentData();
                            }
                        }
                    });

                    this.$watch('availableClubs', (clubs) => {
                        if (clubs.length > 0 && this.currentPage.startsWith('/dash/membri')) {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                        }
                    });

                    if (this.currentPage.startsWith('/dash/membri')) {
                        const h = syncFromHash();
                        applyFiltersAndFetch(h);
                        if (this.user?.role === 'administrator') this.fetchDependentData();
                    }
                    
                    window.addEventListener('clubs-updated', () => {
                        if (this.user?.role === 'administrator') this.fetchDependentData();
                    });
                    
                    this.$watch('filters.club_id', async (val) => {
                        if (this.currentPage.startsWith('/dash/membri')) {
                            if (val) {
                                await this.fetchFilterTeams(val);
                            } else {
                                this.availableFilterTeams = [];
                                this.filters.team_id = '';
                            }
                        }
                    });

                    this.$watch('filters.team_id', async (val) => {
                        if (this.currentPage.startsWith('/dash/membri')) {
                            if (val) {
                                await this.fetchFilterSquads(val);
                            } else {
                                this.availableFilterSquads = [];
                                this.filters.squad_id = '';
                            }
                        }
                    });

                    this.$watch('form.club_id', async (val) => {
                        if (this.showModal) await this.fetchTeamsBasedOnClub();
                    });
                    this.$watch('form.role', async (val) => {
                        if (this.showModal && (val === 'sportiv' || val === 'antrenor')) await this.fetchTeamsBasedOnClub();
                    });
                    this.$watch('form.team_ids', async (val) => {
                        if (this.showModal) await this.fetchSquadsBasedOnTeams();
                    });
                    this.$watch('showModal', (val) => {
                        if (!val) this.updateHash();
                    });
                    window.addEventListener('hashchange', () => {
                        this.processHashActions();
                    });
                },

                processHashActions() {
                    if (!this.currentPage.startsWith('/dash/membri')) return;
                    try {
                        const hp = new URLSearchParams(window.location.hash.substring(1));
                        const action = hp.get('action');
                        const id = hp.get('id');
                        
                        if (action === 'add' && !this.showModal) {
                            this.openModal();
                        } else if (action === 'edit' && id && !this.showModal) {
                            const target = this.users.find(u => u.id == id);
                            if (target) this.openModal(target);
                        } else if (action === 'delete' && id) {
                            const target = this.users.find(u => u.id == id);
                            if (target) {
                                setTimeout(() => { this.deleteUser(id); }, 100);
                            }
                            this.updateHash();
                        }
                    } catch(e) {}
                },

                updateHash(action = null, targetId = null) {
                    const params = new URLSearchParams();
                    if (this.filters.role) params.append('role', this.filters.role);
                    if (this.filters.club_id) params.append('club_id', this.filters.club_id);
                    if (this.filters.team_id) params.append('team_id', this.filters.team_id);
                    if (this.filters.squad_id) params.append('squad_id', this.filters.squad_id);
                    if (action) params.append('action', action);
                    if (targetId) params.append('id', targetId);
                    
                    const newHash = params.toString() ? '#' + params.toString() : '';
                    if (window.location.hash !== newHash) {
                        history.replaceState(null, null, newHash || window.location.pathname);
                    }
                },

                openModal(userToEdit = null) {
                    this.error = null;
                    if(userToEdit) {
                        this.form.id = userToEdit.id;
                        this.form.name = userToEdit.name;
                        this.form.email = userToEdit.email;
                        this.form.role = userToEdit.role;
                        this.form.club_id = userToEdit.club_id || '';
                        this.form.is_active = !!userToEdit.is_active;
                        this.form.team_ids = userToEdit.teams ? userToEdit.teams.map(t => t.id) : [];
                        this.form.squad_ids = userToEdit.squads ? userToEdit.squads.map(s => s.id) : [];
                        this.form.password = ''; // empty default, typed only to override
                        this.updateHash('edit', userToEdit.id);
                        this.fetchTeamsBasedOnClub().then(() => {
                            if (this.form.team_ids.length > 0) this.fetchSquadsBasedOnTeams();
                        });
                    } else {
                        this.form.id = null;
                        this.form.name = '';
                        this.form.email = '';
                        this.form.role = '';
                        this.form.club_id = '';
                        this.form.password = '';
                        this.form.team_ids = [];
                        this.form.squad_ids = [];
                        this.availableSquads = [];
                        this.form.is_active = true;
                        this.updateHash('add');
                    }
                    this.showModal = true;
                },

                async fetchUsers() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.filters.role) params.append('role', this.filters.role);
                        if (this.filters.club_id) params.append('club_id', this.filters.club_id);
                        if (this.filters.team_id) params.append('team_id', this.filters.team_id);
                        if (this.filters.squad_id) params.append('squad_id', this.filters.squad_id);

                        const res = await fetch(`/api/users?${params.toString()}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.users = payload.data;
                            this.processHashActions();
                        }
                    } catch (e) {}
                    this.loading = false;
                },

                async fetchDependentData() {
                    try {
                        const res = await fetch('/api/clubs', {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableClubs = payload.data;
                        }
                    } catch(e) {}
                },

                async fetchFilterTeams(clubIdStr) {
                    if(!clubIdStr) return;
                    try {
                        const res = await fetch(`/api/teams?club_id=${clubIdStr}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableFilterTeams = payload.data;
                        }
                    } catch (e) {}
                },

                async fetchFilterSquads(teamIdStr) {
                    if(!teamIdStr) return;
                    try {
                        const res = await fetch(`/api/squads?team_id=${teamIdStr}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableFilterSquads = payload.data;
                        }
                    } catch (e) {}
                },

                async fetchTeamsBasedOnClub() {
                    const cid = this.form.club_id || (this.user?.role === 'manager' ? this.user.club_id : null);
                    if (!cid) {
                        this.availableTeams = [];
                        return;
                    }
                    this.loadingTeams = true;
                    try {
                        const res = await fetch(`/api/teams?club_id=${cid}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableTeams = payload.data;
                        }
                    } catch (e) {}
                    this.loadingTeams = false;
                },

                async fetchSquadsBasedOnTeams() {
                    if (this.form.team_ids.length === 0) {
                        this.availableSquads = [];
                        return;
                    }
                    this.loadingSquads = true;
                    try {
                        let squadsRaw = [];
                        for(let tid of this.form.team_ids) {
                            const res = await fetch(`/api/squads?team_id=${tid}`, {
                                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                            });
                            if(res.ok) {
                                const payload = await res.json();
                                squadsRaw.push(...payload.data);
                            }
                        }
                        this.availableSquads = squadsRaw;
                    } catch (e) {}
                    this.loadingSquads = false;
                },

                async saveUser() {
                    this.saving = true;
                    this.error = null;
                    
                    if (this.form.role === 'administrator') this.form.club_id = '';
                    
                    const isEdit = !!this.form.id;
                    const url = isEdit ? `/api/users/${this.form.id}` : '/api/users';
                    const method = isEdit ? 'PUT' : 'POST';
                    
                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: { 
                                'Accept': 'application/json', 'Content-Type': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                            },
                            body: JSON.stringify({
                                name: this.form.name,
                                email: this.form.email,
                                role: this.form.role,
                                password: this.form.password,
                                is_active: this.form.is_active,
                                club_id: this.form.club_id || null,
                                team_ids: this.form.team_ids,
                                squad_ids: this.form.squad_ids
                            })
                        });
                        
                        const payload = await res.json();
                        
                        if(res.ok) {
                            if(isEdit) {
                                const idx = this.users.findIndex(u => u.id === this.form.id);
                                if(idx !== -1) this.users[idx] = payload.data;
                                window.showToast('Membru actualizat cu succes!');
                            } else {
                                this.fetchUsers();
                                window.showToast('Membru creat cu succes!');
                            }
                            this.showModal = false;
                            this.form = { id: null, name: '', email: '', role: '', club_id: '', password: '', is_active: true, team_ids: [], squad_ids: [] };
                            this.availableSquads = [];
                        } else {
                            this.error = payload.message || 'Eroare la salvare.';
                            window.showToast(this.error, 'error');
                        }
                    } catch (e) { 
                        this.error = "Eroare rețea."; 
                        window.showToast(this.error, 'error');
                    }
                    
                    this.saving = false;
                },

                async deleteUser(id) {
                    if(!confirm('Sigur dorești să ștergi acest Membru? Această acțiune este ireversibilă.')) return;
                    try {
                        const res = await fetch(`/api/users/${id}`, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        const payload = await res.json();
                        if(!res.ok) {
                            window.showToast(payload.message || 'Nu poți șterge acest Membru.', 'error');
                            return;
                        }
                        window.showToast('Membru șters cu succes!');
                        this.fetchUsers();
                    } catch(e) {
                        window.showToast('A apărut o eroare la ștergere.', 'error');
                    }
                },

                async impersonateUser(user) {
                    if(!confirm(`Ești sigur că vrei să te loghezi ca ${user.name}?`)) return;
                    
                    try {
                        const res = await fetch(`/api/impersonate/${user.id}`, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        const payload = await res.json();
                        if(res.ok) {
                            // Salvam tokenul de admin original pentru restabilire
                            localStorage.setItem('original_admin_token', localStorage.getItem('auth_token'));
                            
                            // Inlocuim tokenul activ cu cel al Membruului
                            localStorage.setItem('auth_token', payload.token);
                            
                            // Reincarcam aplicatia complet
                            window.location.reload();
                        } else {
                            alert(payload.message || 'Eroare la impersonare.');
                        }
                    } catch(e) {
                        alert('Eroare de rețea la impersonare.');
                    }
                },

                // --- User Subscriptions Management ---
                async fetchAvailableSubscriptions(clubId) {
                    if (!clubId) {
                        this.availableSubscriptions = [];
                        return;
                    }
                    try {
                        const res = await fetch(`/api/subscriptions?club_id=${clubId}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if (res.ok) {
                            const payload = await res.json();
                            this.availableSubscriptions = payload.data;
                        }
                    } catch (e) {}
                },

                openSubscriptionModal(user) {
                    this.subscriptionError = null;
                    this.subscriptionForm = {
                        id: null, // Pentru editare
                        user_id: user.id,
                        user_name: user.name,
                        subscription_id: '',
                        starts_at: new Date().toISOString().split('T')[0],
                        status: 'active_pending',
                        current_subscription: user.active_subscription || null
                    };
                    this.fetchAvailableSubscriptions(user.club_id);
                    this.showSubscriptionModal = true;
                },

                editUserSubscription(sub) {
                    this.subscriptionError = null;
                    
                    // Formatam data corect pentru inputul de tip date (YYYY-MM-DD)
                    let startDate = '';
                    if (sub.starts_at) {
                        startDate = sub.starts_at.split(' ')[0];
                    }

                    this.subscriptionForm = {
                        id: sub.id,
                        user_id: this.historyUser?.id || this.subscriptionForm.user_id,
                        user_name: this.historyUser?.name || this.subscriptionForm.user_name,
                        subscription_id: sub.subscription_id,
                        starts_at: startDate,
                        status: sub.status,
                        current_subscription: null // Ascundem secțiunea de status în editare simplă
                    };
                    
                    if (this.historyUser?.club_id) {
                        this.fetchAvailableSubscriptions(this.historyUser.club_id);
                    }
                    
                    this.showSubscriptionHistoryModal = false;
                    this.showSubscriptionModal = true;
                },

                async deleteUserSubscription(id) {
                    if(!confirm('Sigur dorești să ștergi acest înregistrare de abonament?')) return;
                    
                    try {
                        const res = await fetch(`/api/user-subscriptions/${id}`, {
                            method: 'DELETE',
                            headers: { 
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                            }
                        });
                        
                        if(res.ok) {
                            this.fetchUsers();
                            this.showSubscriptionHistoryModal = false;
                            window.showToast('Înregistrare abonament ștearsă.');
                        } else {
                            const payload = await res.json();
                            window.showToast(payload.message || 'Eroare la ștergere.', 'error');
                        }
                    } catch (e) {
                        window.showToast('Eroare rețea.', 'error');
                    }
                },

                async saveUserSubscription() {
                    this.savingSubscription = true;
                    this.subscriptionError = null;
                    
                    const isEdit = !!this.subscriptionForm.id;
                    const url = isEdit ? `/api/user-subscriptions/${this.subscriptionForm.id}` : '/api/user-subscriptions';
                    const method = isEdit ? 'PUT' : 'POST';

                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: { 
                                'Accept': 'application/json', 'Content-Type': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                            },
                            body: JSON.stringify({
                                user_id: this.subscriptionForm.user_id,
                                subscription_id: this.subscriptionForm.subscription_id,
                                starts_at: this.subscriptionForm.starts_at,
                                status: this.subscriptionForm.status
                            })
                        });
                        
                        const payload = await res.json();
                        
                        if(res.ok) {
                            this.fetchUsers();
                            this.showSubscriptionModal = false;
                            window.showToast(isEdit ? 'Abonament actualizat!' : 'Abonament alocat cu succes!');
                        } else {
                            if (res.status === 422 && payload.errors) {
                                const errors = Object.values(payload.errors).flat();
                                this.subscriptionError = errors.join(' ');
                            } else {
                                this.subscriptionError = payload.message || 'Eroare la alocarea abonamentului.';
                            }
                            window.showToast(this.subscriptionError, 'error');
                        }
                    } catch (e) { 
                        this.subscriptionError = "Eroare rețea."; 
                        window.showToast(this.subscriptionError, 'error');
                    }
                    
                    this.savingSubscription = false;
                },

                openSubscriptionHistory(user) {
                    this.historyUser = user;
                    this.showSubscriptionHistoryModal = true;
                },

                formatDate(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('ro-RO', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric' 
                    });
                },

                async updateSubscriptionStatus(userSubscriptionId, status) {
                    this.savingSubscription = true;
                    this.subscriptionError = null;
                    
                    try {
                        const res = await fetch(`/api/user-subscriptions/${userSubscriptionId}/status`, {
                            method: 'PATCH',
                            headers: { 
                                'Accept': 'application/json', 'Content-Type': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                            },
                            body: JSON.stringify({ status: status })
                        });
                        
                        const payload = await res.json();
                        
                        if(res.ok) {
                            this.fetchUsers();
                            this.showSubscriptionModal = false;
                            window.showToast('Status abonament actualizat!');
                        } else {
                            this.subscriptionError = payload.message || 'Eroare la actualizarea statusului.';
                            window.showToast(this.subscriptionError, 'error');
                        }
                    } catch (e) { 
                        this.subscriptionError = "Eroare rețea."; 
                        window.showToast(this.subscriptionError, 'error');
                    }
                    
                    this.savingSubscription = false;
                }
            }));

            // ------- Gestiune Grupe (Teams) -------
            Alpine.data('teamManager', () => ({
                teams: [],
                availableClubs: [],
                loading: false,
                saving: false,
                showModal: false,
                error: null,
                form: { id: null, name: '', club_id: '' },
                filters: { club_id: '' },

                init() {
                    const syncFromHash = () => {
                        let hashClub = '';
                        if (window.location.hash && window.location.pathname.startsWith('/dash/grupe')) {
                            try {
                                const hp = new URLSearchParams(window.location.hash.substring(1));
                                hashClub = hp.get('club_id') || '';
                            } catch(e) {}
                        }
                        return hashClub;
                    };

                    const applyFiltersAndFetch = (h) => {
                        this.filters.club_id = h;
                        
                        setTimeout(() => {
                            const cSelect = document.getElementById('teamFilterClub');
                            if (cSelect) cSelect.value = h;
                            this.fetchTeams();
                        }, 50);
                    };

                    this.$watch('currentPage', value => {
                        if (value === '/dash/grupe') {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                            if (this.user?.role === 'administrator' && this.availableClubs.length === 0) {
                                this.fetchClubs();
                            }
                        } else if (!value.startsWith('/dash/grupe')) {
                            this.filters.club_id = '';
                        }
                    });

                    this.$watch('user', (usr) => {
                        if (usr && usr.role === 'administrator' && this.currentPage.startsWith('/dash/grupe')) {
                            if (this.availableClubs.length === 0) this.fetchClubs();
                        }
                    });

                    this.$watch('availableClubs', (clubs) => {
                        if (clubs.length > 0 && this.currentPage.startsWith('/dash/grupe')) {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                        }
                    });

                    if (this.currentPage.startsWith('/dash/grupe')) {
                        const h = syncFromHash();
                        applyFiltersAndFetch(h);
                        if (this.user?.role === 'administrator') this.fetchClubs();
                    }

                    window.addEventListener('clubs-updated', () => {
                        if (this.user?.role === 'administrator') this.fetchClubs();
                    });
                    this.$watch('showModal', (val) => {
                        if (!val) this.updateHash();
                    });
                    window.addEventListener('hashchange', () => {
                        this.processHashActions();
                    });
                },

                processHashActions() {
                    if (!this.currentPage.startsWith('/dash/grupe')) return;
                    try {
                        const hp = new URLSearchParams(window.location.hash.substring(1));
                        const action = hp.get('action');
                        const id = hp.get('id');
                        
                        if (action === 'add' && !this.showModal) {
                            this.openModal();
                        } else if (action === 'edit' && id && !this.showModal) {
                            const target = this.teams.find(t => t.id == id);
                            if (target) this.openModal(target);
                        } else if (action === 'delete' && id) {
                            const target = this.teams.find(t => t.id == id);
                            if (target) {
                                setTimeout(() => { this.deleteTeam(id); }, 100);
                            }
                            this.updateHash();
                        }
                    } catch(e) {}
                },

                updateHash(action = null, targetId = null) {
                    const params = new URLSearchParams();
                    if (this.filters.club_id) params.append('club_id', this.filters.club_id);
                    if (action) params.append('action', action);
                    if (targetId) params.append('id', targetId);
                    
                    const newHash = params.toString() ? '#' + params.toString() : '';
                    if (window.location.hash !== newHash) {
                        history.replaceState(null, null, newHash || window.location.pathname);
                    }
                },

                openModal(team = null) {
                    this.error = null;
                    if(team) {
                        this.form.id = team.id;
                        this.form.name = team.name;
                        this.form.club_id = team.club_id;
                        this.updateHash('edit', team.id);
                    } else {
                        this.form.id = null;
                        this.form.name = '';
                        this.form.club_id = '';
                        this.updateHash('add');
                    }
                    this.showModal = true;
                },

                async fetchClubs() {
                    try {
                        const res = await fetch('/api/clubs', {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableClubs = payload.data;
                        }
                    } catch(e) {}
                },

                async fetchTeams() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.filters.club_id) params.append('club_id', this.filters.club_id);

                        const res = await fetch(`/api/teams?${params.toString()}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.teams = payload.data;
                            this.processHashActions();
                        }
                    } catch (e) { console.error(e); }
                    this.loading = false;
                },

                async saveTeam() {
                    this.saving = true;
                    this.error = null;
                    
                    const isEdit = !!this.form.id;
                    const url = isEdit ? `/api/teams/${this.form.id}` : '/api/teams';
                    const method = isEdit ? 'PUT' : 'POST';
                    
                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: { 
                                'Accept': 'application/json', 
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                            },
                            body: JSON.stringify({ 
                                name: this.form.name,
                                club_id: this.form.club_id || null
                            })
                        });
                        
                        const payload = await res.json();
                        
                        if(res.ok) {
                            this.fetchTeams();
                            this.showModal = false;
                            window.showToast(isEdit ? 'Grupă actualizată cu succes!' : 'Grupă creată cu succes!');
                        } else {
                            this.error = payload.message || 'Eroare la salvare.';
                            window.showToast(this.error, 'error');
                        }
                    } catch (e) { 
                        this.error = "Eroare de rețea."; 
                        window.showToast(this.error, 'error');
                    }
                    this.saving = false;
                },

                async deleteTeam(id) {
                    if(!confirm('Sigur dorești ștergerea acestei grupe? Acțiunea e ireversibilă!')) return;
                    
                    try {
                        const res = await fetch(`/api/teams/${id}`, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            this.teams = this.teams.filter(t => t.id !== id);
                            window.showToast('Grupă ștearsă cu succes!');
                        } else {
                            const data = await res.json();
                            window.showToast(data.message || 'Eroare la ștergere. Posibil grupa are membri asociați.', 'error');
                        }
                    } catch (e) { window.showToast('A apărut o eroare de rețea.', 'error'); }
                }
            }));

            // ------- Gestiune Echipe (Squads) -------
            Alpine.data('squadManager', () => ({
                squads: [],
                availableClubs: [],
                availableModalTeams: [], // Grupele încărcate pentru dropdown-ul din modal de creare
                loading: false,
                saving: false,
                showModal: false,
                error: null,
                form: { id: null, name: '', club_id: '', team_id: '' },
                filters: { club_id: '' },

                init() {
                    const syncFromHash = () => {
                        let hashClub = '';
                        if (window.location.hash && window.location.pathname.startsWith('/dash/echipe')) {
                            try {
                                const hp = new URLSearchParams(window.location.hash.substring(1));
                                hashClub = hp.get('club_id') || '';
                            } catch(e) {}
                        }
                        return hashClub;
                    };

                    const applyFiltersAndFetch = (h) => {
                        this.filters.club_id = h;
                        this.fetchSquads();
                    };

                    this.$watch('currentPage', value => {
                        if (value === '/dash/echipe') {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                            if (this.user?.role === 'administrator' && this.availableClubs.length === 0) {
                                this.fetchClubs();
                            }
                        } else if (!value.startsWith('/dash/echipe')) {
                            this.filters.club_id = '';
                        }
                    });

                    this.$watch('user', (usr) => {
                        if (usr && usr.role === 'administrator' && this.currentPage.startsWith('/dash/echipe')) {
                            if (this.availableClubs.length === 0) this.fetchClubs();
                        }
                    });
                    
                    this.$watch('availableClubs', (clubs) => {
                        if (clubs.length > 0 && this.currentPage.startsWith('/dash/echipe')) {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                        }
                    });

                    if (this.currentPage.startsWith('/dash/echipe')) {
                        const h = syncFromHash();
                        applyFiltersAndFetch(h);
                        if (this.user?.role === 'administrator') this.fetchClubs();
                    }

                    window.addEventListener('clubs-updated', () => {
                        if (this.user?.role === 'administrator') this.fetchClubs();
                    });

                    this.$watch('showModal', (val) => {
                        if (!val) this.updateHash();
                    });
                    window.addEventListener('hashchange', () => {
                        this.processHashActions();
                    });
                },

                processHashActions() {
                    if (!this.currentPage.startsWith('/dash/echipe')) return;
                    try {
                        const hp = new URLSearchParams(window.location.hash.substring(1));
                        const action = hp.get('action');
                        const id = hp.get('id');
                        
                        if (action === 'add' && !this.showModal) {
                            this.openModal();
                        } else if (action === 'edit' && id && !this.showModal) {
                            const target = this.squads.find(s => s.id == id);
                            if (target) this.openModal(target);
                        } else if (action === 'delete' && id) {
                            const target = this.squads.find(s => s.id == id);
                            if (target) {
                                setTimeout(() => { this.deleteSquad(id); }, 100);
                            }
                            this.updateHash();
                        }
                    } catch(e) {}
                },

                updateHash(action = null, targetId = null) {
                    const params = new URLSearchParams();
                    if (this.filters.club_id) params.append('club_id', this.filters.club_id);
                    if (action) params.append('action', action);
                    if (targetId) params.append('id', targetId);
                    
                    const newHash = params.toString() ? '#' + params.toString() : '';
                    if (window.location.hash !== newHash) {
                        history.replaceState(null, null, newHash || window.location.pathname);
                    }
                },

                async fetchModalTeams() {
                    // Când selectezi un club in modal, vrem să arătăm doar grupele acelui club
                    this.availableModalTeams = [];
                    this.form.team_id = ''; // resetare selecție
                    if (this.user?.role === 'administrator' && !this.form.club_id) return;
                    
                    try {
                        let url = '/api/teams';
                        if (this.form.club_id) {
                            url += `?club_id=${this.form.club_id}`;
                        }

                        const res = await fetch(url, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if (res.ok) {
                            const payload = await res.json();
                            this.availableModalTeams = payload.data;
                        }
                    } catch(e) {}
                },

                openModal(squad = null) {
                    this.error = null;
                    if(squad) {
                        this.form.id = squad.id;
                        this.form.name = squad.name;
                        this.form.club_id = squad.team?.club_id || '';
                        this.updateHash('edit', squad.id);
                        
                        // Populăm echipele pentru acel club + selectăm grupa
                        if (this.form.club_id || this.user?.role === 'manager') {
                            // Dacă e manager, știm sigur că tragem toate echipele din clubul lui implicit (via empty club_id query for teams sau backend filter).
                            // Pentru admin, o chemăm explicit.
                            this.fetchModalTeams().then(() => {
                                this.form.team_id = squad.team_id;
                            });
                        } else {
                            this.form.team_id = squad.team_id;
                        }
                    } else {
                        this.form.id = null;
                        this.form.name = '';
                        this.form.club_id = '';
                        this.form.team_id = '';
                        this.availableModalTeams = [];
                        
                        // Dacă e manager, încarcă direct grupele lui (fără să trebuiască selecteze club)
                        if (this.user?.role === 'manager') {
                            this.fetchModalTeams();
                        }
                        this.updateHash('add');
                    }
                    this.showModal = true;
                },

                async fetchClubs() {
                    try {
                        const res = await fetch('/api/clubs', {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableClubs = payload.data;
                        }
                    } catch(e) {}
                },

                async fetchSquads() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.filters.club_id) params.append('club_id', this.filters.club_id);

                        const res = await fetch(`/api/squads?${params.toString()}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.squads = payload.data;
                            this.processHashActions();
                        }
                    } catch(e) {}
                    this.loading = false;
                },

                async saveSquad() {
                    this.saving = true;
                    this.error = null;
                    
                    const isEdit = !!this.form.id;
                    const url = isEdit ? `/api/squads/${this.form.id}` : '/api/squads';
                    const method = isEdit ? 'PUT' : 'POST';
                    
                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: { 
                                'Accept': 'application/json', 
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                            },
                            body: JSON.stringify({ 
                                name: this.form.name,
                                team_id: this.form.team_id
                            })
                        });
                        
                        const payload = await res.json();
                        
                        if(res.ok) {
                            this.fetchSquads();
                            this.showModal = false;
                            window.showToast(isEdit ? 'Echipă actualizată cu succes!' : 'Echipă creată cu succes!');
                        } else {
                            this.error = payload.message || 'Eroare la salvare.';
                            window.showToast(this.error, 'error');
                        }
                    } catch (e) { 
                        this.error = "Eroare de rețea."; 
                        window.showToast(this.error, 'error');
                    }
                    this.saving = false;
                },

                async deleteSquad(id) {
                    if(!confirm('Sigur dorești ștergerea acestei echipe? Acțiunea e ireversibilă!')) return;
                    
                    try {
                        const res = await fetch(`/api/squads/${id}`, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            this.squads = this.squads.filter(s => s.id !== id);
                            window.showToast('Echipă ștearsă cu succes!');
                        } else {
                            const data = await res.json();
                            window.showToast(data.message || 'Eroare la ștergere. Posibil echipa are membri asociați.', 'error');
                        }
                    } catch (e) { window.showToast('A apărut o eroare de rețea.', 'error'); }
                }
            }));

            // ------- Gestiune Abonamente (Subscriptions) -------
            Alpine.data('subscriptionManager', () => ({
                subscriptions: [],
                availableClubs: [],
                loading: false,
                saving: false,
                showModal: false,
                error: null,
                form: { id: null, name: '', price: '', period: '1_luna', club_id: '' },
                filters: { club_id: '' },

                init() {
                    const syncFromHash = () => {
                        let hashClub = '';
                        if (window.location.hash && window.location.pathname.startsWith('/dash/abonamente')) {
                            try {
                                const hp = new URLSearchParams(window.location.hash.substring(1));
                                hashClub = hp.get('club_id') || '';
                            } catch(e) {}
                        }
                        return hashClub;
                    };

                    const applyFiltersAndFetch = (h) => {
                        this.filters.club_id = h;
                        this.fetchSubscriptions();
                    };

                    this.$watch('currentPage', value => {
                        if (value === '/dash/abonamente') {
                            const h = syncFromHash();
                            applyFiltersAndFetch(h);
                            if (this.user?.role === 'administrator' && this.availableClubs.length === 0) {
                                this.fetchClubs();
                            }
                        } else if (!value.startsWith('/dash/abonamente')) {
                            this.filters.club_id = '';
                        }
                    });

                    // Avoid errors if user is null initially (before fetch completes)
                    this.$watch('user', (usr) => {
                        if (usr && usr?.role === 'administrator' && this.currentPage.startsWith('/dash/abonamente')) {
                            if (this.availableClubs.length === 0) this.fetchClubs();
                        }
                    });

                    if (this.currentPage.startsWith('/dash/abonamente')) {
                        const h = syncFromHash();
                        applyFiltersAndFetch(h);
                        if (this.user && this.user?.role === 'administrator') this.fetchClubs();
                    }

                    this.$watch('showModal', (val) => {
                        if (!val) this.updateHash();
                    });
                    window.addEventListener('hashchange', () => {
                        this.processHashActions();
                    });
                },

                processHashActions() {
                    if (!this.currentPage.startsWith('/dash/abonamente')) return;
                    try {
                        const hp = new URLSearchParams(window.location.hash.substring(1));
                        const action = hp.get('action');
                        const id = hp.get('id');
                        
                        if (action === 'add' && !this.showModal) {
                            this.openModal();
                        } else if (action === 'edit' && id && !this.showModal) {
                            const target = this.subscriptions.find(s => s.id == id);
                            if (target) this.openModal(target);
                        } else if (action === 'delete' && id) {
                            const target = this.subscriptions.find(s => s.id == id);
                            if (target) {
                                setTimeout(() => { this.deleteSubscription(id); }, 100);
                            }
                            this.updateHash();
                        }
                    } catch(e) {}
                },

                updateHash(action = null, targetId = null) {
                    const params = new URLSearchParams();
                    if (this.filters.club_id) params.append('club_id', this.filters.club_id);
                    if (action) params.append('action', action);
                    if (targetId) params.append('id', targetId);
                    
                    const newHash = params.toString() ? '#' + params.toString() : '';
                    if (window.location.hash !== newHash) {
                        history.replaceState(null, null, newHash || window.location.pathname);
                    }
                },

                openModal(sub = null) {
                    this.error = null;
                    if(sub) {
                        this.form.id = sub.id;
                        this.form.name = sub.name;
                        this.form.price = sub.price;
                        this.form.period = sub.period;
                        this.form.club_id = sub.club_id;
                        this.updateHash('edit', sub.id);
                    } else {
                        this.form.id = null;
                        this.form.name = '';
                        this.form.price = '';
                        this.form.period = '1_luna';
                        this.form.club_id = '';
                        this.updateHash('add');
                    }
                    this.showModal = true;
                },

                async fetchClubs() {
                    try {
                        const res = await fetch('/api/clubs', {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.availableClubs = payload.data;
                        }
                    } catch(e) {}
                },

                async fetchSubscriptions() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.filters.club_id) params.append('club_id', this.filters.club_id);

                        const res = await fetch(`/api/subscriptions?${params.toString()}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            const payload = await res.json();
                            this.subscriptions = payload.data;
                            this.processHashActions();
                        }
                    } catch(e) {}
                    this.loading = false;
                },

                async saveSubscription() {
                    this.saving = true;
                    this.error = null;
                    
                    const isEdit = !!this.form.id;
                    const url = isEdit ? `/api/subscriptions/${this.form.id}` : '/api/subscriptions';
                    const method = isEdit ? 'PUT' : 'POST';
                    
                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: { 
                                'Accept': 'application/json', 
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                            },
                            body: JSON.stringify({ 
                                name: this.form.name,
                                price: this.form.price,
                                period: this.form.period,
                                club_id: this.form.club_id
                            })
                        });
                        
                        const payload = await res.json();
                        
                        if(res.ok) {
                            this.fetchSubscriptions();
                            this.showModal = false;
                            window.showToast(isEdit ? 'Abonament actualizat cu succes!' : 'Abonament creat cu succes!');
                        } else {
                            this.error = payload.message || 'Eroare la salvare.';
                            window.showToast(this.error, 'error');
                        }
                    } catch (e) { 
                        this.error = "Eroare de rețea."; 
                        window.showToast(this.error, 'error');
                    }
                    this.saving = false;
                },

                async deleteSubscription(id) {
                    if(!confirm('Sigur dorești ștergerea acestui abonament de club?')) return;
                    
                    try {
                        const res = await fetch(`/api/subscriptions/${id}`, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                        });
                        if(res.ok) {
                            this.subscriptions = this.subscriptions.filter(s => s.id !== id);
                            window.showToast('Abonament șters cu succes!');
                        } else {
                            const data = await res.json();
                            window.showToast(data.message || 'Eroare la ștergere. Posibil există membri activi asociați.', 'error');
                        }
                    } catch (e) { window.showToast('A apărut o eroare de rețea.', 'error'); }
                }
            }));

            // ------- Kernel SPA Dashboard -------
            Alpine.data('dashboard', () => ({
                user: null,
                isLoading: true,
                token: null,
                isMobileMenuOpen: false,
                isImpersonating: false,
                currentPage: window.location.pathname, // Route Tracker Simplu

                    getPageTitle() {
                        if(this.currentPage === '/dash') return 'Acasă';
                        if(this.currentPage.startsWith('/dash/cluburi')) return 'Management Cluburi';
                        if(this.currentPage.startsWith('/dash/membri')) return 'Membri';
                        if(this.currentPage.startsWith('/dash/echipe')) return 'Echipe';
                        if(this.currentPage.startsWith('/dash/abonamente')) return 'Abonamente';
                        return 'Dashboard';
                    },

                navigate(path) {
                    if (this.user) {
                        if (!['administrator', 'manager'].includes(this.user.role) && path !== '/dash') {
                            path = '/dash';
                        }
                        if (this.user.role === 'manager' && path.startsWith('/dash/cluburi')) {
                            path = '/dash';
                        }
                    }
                    this.currentPage = path;
                    
                    // Clear Hash State gracefully on programmatic navigation
                    if (window.location.hash) {
                        window.history.pushState({}, '', path); // Set without hash
                    } else {
                        window.history.pushState({}, '', path);
                    }
                },

                async init() {
                    // Ascultăm schimbările de istoric din browser (Butonul Back/Forward)
                    window.addEventListener('popstate', () => {
                        this.currentPage = window.location.pathname;
                    });
                    
                    // Suport fallback SPA imediat după încărcarea paginii dacă URL-ul este pe vreo subrută
                    const currentPath = window.location.pathname;
                    if (currentPath !== '/dash' && currentPath.startsWith('/dash/')) {
                         this.currentPage = currentPath;
                    }

                    this.token = localStorage.getItem('auth_token');
                    this.isImpersonating = !!localStorage.getItem('original_admin_token');
                    
                    if (!this.token) {
                        window.location.href = '/dash/login';
                        return;
                    }

                    try {
                        const response = await fetch('/api/user', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            }
                        });
                        
                        if (response.ok) {
                            this.user = await response.json();
                            this.isLoading = false;
                            
                            // Security check fallback for deep links
                            if (!['administrator', 'manager'].includes(this.user.role) && this.currentPage !== '/dash') {
                                this.navigate('/dash');
                            } else if (this.user.role === 'manager' && this.currentPage.startsWith('/dash/cluburi')) {
                                this.navigate('/dash');
                            }
                        } else {
                            this.logout(false);
                        }
                    } catch (error) {
                        this.logout(false);
                    }
                },

                async logout(callApi = true) {
                    if (callApi && this.token) {
                        try {
                            await fetch('/api/logout', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Authorization': `Bearer ${this.token}`
                                }
                            });
                        } catch (e) {}
                    }
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('original_admin_token'); // Clear in caz ca era impersonat si da logout manual
                    window.location.href = '/dash/login';
                },

                async leaveImpersonation() {
                    try {
                        const res = await fetch('/api/impersonate-leave', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${this.token}` }
                        });
                        
                        const adminToken = localStorage.getItem('original_admin_token');
                        localStorage.setItem('auth_token', adminToken);
                        localStorage.removeItem('original_admin_token');
                        window.showToast('Ai revenit la contul tău de administrator.');
                        setTimeout(() => window.location.reload(), 1500);
                    } catch (e) {
                        window.showToast('Eroare la delogare din impersonare.', 'error');
                    }
                }
            }));

            Alpine.data('auditManager', () => ({
                logs: [],
                loading: false,
                filters: {
                    event: '',
                    type: '',
                    page: 1
                },
                pagination: {
                    current_page: 1,
                    last_page: 1,
                    total: 0
                },
                showDetailsModal: false,
                selectedLogData: null,

                async init() {
                    this.$watch('currentPage', (val) => {
                        if (val === '/dash/audit') {
                            this.fetchLogs();
                        }
                    });
                    
                    if (this.currentPage === '/dash/audit') {
                        this.fetchLogs();
                    }
                },

                async fetchLogs() {
                    this.loading = true;
                    try {
                        let url = `/api/audit?page=${this.filters.page}`;
                        if (this.filters.event) url += `&event=${this.filters.event}`;
                        if (this.filters.type) url += `&auditable_type=${this.filters.type}`;

                        const res = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                            }
                        });
                        const data = await res.json();
                        
                        if (res.ok) {
                            this.logs = data.data;
                            this.pagination = {
                                current_page: data.current_page,
                                last_page: data.last_page,
                                total: data.total
                            };
                        }
                    } catch (e) {
                        console.error("Audit fetch error", e);
                    } finally {
                        this.loading = false;
                    }
                },

                changePage(page) {
                    if (page < 1 || page > this.pagination.last_page) return;
                    this.filters.page = page;
                    this.fetchLogs();
                },

                openLogDetails(log) {
                    this.selectedLogData = log.event === 'created' ? log.new_values : log.old_values;
                    this.showDetailsModal = true;
                },

                translateAuditKey(key) {
                    const keys = {
                        'name': 'Nume',
                        'email': 'Email',
                        'role': 'Rol',
                        'is_active': 'Activ',
                        'club_id': 'Club',
                        'team_ids': 'Grupe',
                        'squad_ids': 'Echipe',
                        'status': 'Statut',
                        'price': 'Preț',
                        'period': 'Perioadă',
                        'address': 'Adresă',
                        'day_of_week': 'Zi Săptămână',
                        'start_time': 'Oră Start',
                        'end_time': 'Oră Final',
                        'location_id': 'Locație',
                        'coach_id': 'Antrenor',
                        'team_id': 'Grupă',
                        'starts_at': 'Data Început',
                        'expires_at': 'Data Expirare',
                        'subscription_id': 'Tip Abonament'
                    };
                    return keys[key] || key;
                },

                translateAuditValue(key, val) {
                    if (val === null || val === undefined || val === '') return 'N/A';
                    if (val === true || val === '1' || val === 1) return 'Da';
                    if (val === false || val === '0' || val === 0) return 'Nu';
                    
                    if (key === 'status') return this.statusLabels[val] || val;
                    if (key === 'role') {
                        const roles = { 'administrator': 'Administrator', 'manager': 'Manager', 'antrenor': 'Antrenor', 'sportiv': 'Sportiv' };
                        return roles[val] || val;
                    }
                    if (key === 'day_of_week') {
                        const days = { 'luni': 'Luni', 'marti': 'Marți', 'miercuri': 'Miercuri', 'joi': 'Joi', 'vineri': 'Vineri', 'sambata': 'Sâmbătă', 'duminica': 'Duminică' };
                        return days[val] || val;
                    }
                    if (key === 'period') {
                        const periods = { '1_saptamana': '1 Săptămână', '2_saptamani': '2 Săptămâni', '1_luna': '1 Lună', '3_luni': '3 Luni', '6_luni': '6 Luni', '1_an': '1 An' };
                        return periods[val] || val;
                    }

                    return val;
                },

                translateAuditType(type) {
                    const cleanType = type.split('\\').pop();
                    const types = {
                        'User': 'Utilizator',
                        'Club': 'Club',
                        'Subscription': 'Definiție Abonament',
                        'UserSubscription': 'Abonament Membru',
                        'Team': 'Grupă',
                        'Squad': 'Echipă',
                        'Location': 'Locație',
                        'Training': 'Antrenament'
                    };
                    return types[cleanType] || cleanType;
                }
            }));

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

            Alpine.data('trainingManager', () => ({
                trainings: [],
                loading: false,
                saving: false,
                showModal: false,
                editingId: null,
                allClubs: [],
                availableLocations: [],
                availableTeams: [],
                availableCoaches: [],
                error: null,
                filters: {
                    club_id: '',
                    team_id: ''
                },
                formData: {
                    club_id: '',
                    location_id: '',
                    team_id: '',
                    coach_id: '',
                    day_of_week: 'luni',
                    start_time: '18:00',
                    end_time: '20:00'
                },

                async init() {
                    this.$watch('currentPage', (val) => {
                        if (val === '/dash/antrenamente') {
                            this.fetchTrainings();
                            if (this.user?.role === 'administrator') {
                                this.fetchClubs();
                            } else if (this.user?.club_id) {
                                this.formData.club_id = this.user.club_id;
                                this.onClubChange();
                            }
                        }
                    });

                    if (this.currentPage === '/dash/antrenamente') {
                        this.fetchTrainings();
                        if (this.user?.role === 'administrator') {
                            this.fetchClubs();
                        } else if (this.user?.club_id) {
                            this.formData.club_id = this.user.club_id;
                            this.onClubChange();
                        }
                    }

                    this.$watch('formData.club_id', () => {
                        this.onClubChange();
                    });
                },

                async fetchTrainings() {
                    this.loading = true;
                    try {
                        let url = '/api/trainings';
                        const params = new URLSearchParams();
                        
                        const clubId = this.filters.club_id || (this.user?.role === 'manager' ? this.user.club_id : '');
                        if (clubId) params.append('club_id', clubId);
                        if (this.filters.team_id) params.append('team_id', this.filters.team_id);
                        
                        if (params.toString()) url += '?' + params.toString();

                        const res = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                            }
                        });
                        if (res.ok) {
                            this.trainings = await res.json();
                        }
                    } catch (e) {
                        console.error("Training fetch error", e);
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

                async onClubChange() {
                    const clubId = this.formData.club_id || (this.user?.role === 'manager' ? this.user.club_id : '');
                    if (!clubId) {
                        this.availableLocations = [];
                        this.availableTeams = [];
                        this.availableCoaches = [];
                        return;
                    }

                    // Fetch Locations for club
                    fetch(`/api/locations?club_id=${clubId}`, {
                        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                    }).then(r => r.json()).then(data => this.availableLocations = data);

                    // Fetch Teams for club
                    fetch(`/api/teams?club_id=${clubId}`, {
                        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                    }).then(r => r.json()).then(data => this.availableTeams = data.data || data);

                    // Fetch Coaches for club (antrenori and managers)
                    fetch(`/api/users?club_id=${clubId}&role=antrenor,manager`, {
                        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                    }).then(r => r.json()).then(data => this.availableCoaches = data.data || data);
                },

                openModal(t = null) {
                    this.error = null;
                    if (t) {
                        this.editingId = t.id;
                        this.formData = {
                            club_id: t.club_id,
                            location_id: t.location_id,
                            team_id: t.team_id,
                            coach_id: t.coach_id,
                            day_of_week: t.day_of_week,
                            start_time: t.start_time.substring(0,5),
                            end_time: t.end_time.substring(0,5)
                        };
                        this.onClubChange();
                    } else {
                        this.editingId = null;
                        this.formData = {
                            club_id: this.user?.role === 'manager' ? this.user.club_id : '',
                            location_id: '',
                            team_id: '',
                            coach_id: '',
                            day_of_week: 'luni',
                            start_time: '18:00',
                            end_time: '20:00'
                        };
                        if (this.user?.role === 'manager' || this.formData.club_id) {
                            this.onClubChange();
                        }
                    }
                    this.showModal = true;
                },

                async saveTraining() {
                    this.saving = true;
                    this.error = null;
                    try {
                        const method = this.editingId ? 'PUT' : 'POST';
                        const url = this.editingId ? `/api/trainings/${this.editingId}` : '/api/trainings';

                        const payload = {...this.formData};
                        if (this.user?.role === 'manager') payload.club_id = this.user.club_id;

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
                            this.fetchTrainings();
                            window.showToast(this.editingId ? 'Antrenament actualizat!' : 'Antrenament programat!');
                        } else {
                            const data = await res.json();
                            this.error = data.message || "Eroare la salvarea antrenamentului.";
                            window.showToast(this.error, 'error');
                        }
                    } catch (e) {
                        window.showToast("Eroare de rețea la salvarea antrenamentului.", 'error');
                    } finally {
                        this.saving = false;
                    }
                },

                async deleteTraining(id) {
                    if (!confirm("Ești sigur că vrei să ștergi acest antrenament?")) return;

                    try {
                        const res = await fetch(`/api/trainings/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                            }
                        });

                        if (res.ok) {
                            this.fetchTrainings();
                            window.showToast('Antrenament șters cu succes!');
                        } else {
                            const data = await res.json();
                            window.showToast(data.message || 'Eroare la ștergere.', 'error');
                        }
                    } catch (e) {
                        window.showToast("Eroare de rețea la ștergere.", 'error');
                    }
                }
            }));
        });
    </script>
