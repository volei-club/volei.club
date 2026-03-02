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
    photoPreview: null,
    form: { id: null, name: '', email: '', phone: '', role: '', club_id: '', password: '', is_active: true, team_ids: [], squad_ids: [], child_ids: [], photo: null, photo_url: null },
    availableStudents: [],
    loadingStudents: false,
    
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
        'scheduled': 'Programat',
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
                const cSelect = document.getElementById('userFilterClub');
                const tSelect = document.getElementById('userFilterTeam');
                const sSelect = document.getElementById('userFilterSquad');
                if (cSelect) cSelect.value = h.club;
                if (tSelect) tSelect.value = h.team;
                if (sSelect) sSelect.value = h.squad;
            }, 50);
            this.fetchUsers();
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
            if (this.showModal) {
                if (this.form.role === 'antrenor') {
                    await this.fetchSquadsBasedOnClub();
                } else {
                    await this.fetchTeamsBasedOnClub();
                }
                if (this.form.role === 'parinte') await this.fetchAvailableStudents(val);
            }
        });
        this.$watch('form.role', async (val) => {
            if (this.showModal) {
                if (val === 'antrenor') {
                    await this.fetchSquadsBasedOnClub();
                } else if (val === 'sportiv') {
                    await this.fetchTeamsBasedOnClub();
                } else if (val === 'parinte') {
                    await this.fetchAvailableStudents(this.form.club_id || this.user?.club_id);
                }
            }
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
        this.photoPreview = null;
        if(userToEdit) {
            this.form.id = userToEdit.id;
            this.form.name = userToEdit.name;
            this.form.email = userToEdit.email;
            this.form.role = userToEdit.role;
            this.form.club_id = userToEdit.club_id || '';
            this.form.phone = userToEdit.phone || '';
            this.form.is_active = !!userToEdit.is_active;
            this.form.team_ids = userToEdit.teams ? userToEdit.teams.map(t => t.id) : [];
            this.form.squad_ids = userToEdit.squads ? userToEdit.squads.map(s => s.id) : [];
            this.form.child_ids = userToEdit.children ? userToEdit.children.map(c => c.id) : [];
            this.form.password = ''; 
            this.form.photo = null;
            this.form.photo_url = userToEdit.photo;
            this.updateHash('edit', userToEdit.id);
            
            // Incarcam datele dependente
            if (this.form.role === 'parinte') {
                this.fetchAvailableStudents(this.form.club_id);
            }
            
            this.fetchTeamsBasedOnClub().then(() => {
                if (this.form.team_ids.length > 0) this.fetchSquadsBasedOnTeams();
            });
        } else {
            this.form.id = null;
            this.form.name = '';
            this.form.email = '';
            this.form.phone = '';
            this.form.role = '';
            this.form.club_id = '';
            this.form.password = '';
            this.form.team_ids = [];
            this.form.squad_ids = [];
            this.form.child_ids = [];
            this.form.photo = null;
            this.form.photo_url = null;
            this.availableSquads = [];
            this.availableStudents = [];
            this.form.is_active = true;
            this.updateHash('add');
        }
        this.showModal = true;
    },

    handlePhotoSelect(e) {
        const file = e.target.files[0];
        if (file) {
            window.dispatchEvent(new CustomEvent('open-cropper', {
                detail: {
                    file: file,
                    callback: (croppedFile, previewUrl) => {
                        this.form.photo = croppedFile;
                        this.photoPreview = previewUrl;
                    }
                }
            }));
        }
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
            this.form.squad_ids = [];
            return;
        }
        this.loadingSquads = true;
        try {
            const teamIdsStr = this.form.team_ids.join(',');
            const res = await fetch(`/api/squads?team_id=${teamIdsStr}`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if (res.ok) {
                const payload = await res.json();
                this.availableSquads = payload.data;
                // Curățăm selecțiile care nu mai sunt valide
                const validIds = this.availableSquads.map(s => s.id);
                this.form.squad_ids = this.form.squad_ids.filter(id => validIds.includes(id));
            }
        } catch (e) {}
        this.loadingSquads = false;
    },

    async fetchSquadsBasedOnClub() {
        const cid = this.form.club_id || (this.user?.role === 'manager' ? this.user.club_id : null);
        if (!cid) {
            this.availableSquads = [];
            return;
        }
        this.loadingSquads = true;
        try {
            const res = await fetch(`/api/squads?club_id=${cid}`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if (res.ok) {
                const payload = await res.json();
                this.availableSquads = payload.data;
            }
        } catch (e) {}
        this.loadingSquads = false;
    },

    async fetchAvailableStudents(clubId) {
        const cId = clubId || this.user?.club_id;
        if (!cId || this.form.role !== 'parinte') {
            this.availableStudents = [];
            return;
        }
        this.loadingStudents = true;
        try {
            const res = await fetch(`/api/users?club_id=${cId}&role=sportiv`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if (res.ok) {
                const payload = await res.json();
                this.availableStudents = payload.data;
            }
        } catch (e) {}
        this.loadingStudents = false;
    },
    async saveUser() {
        this.saving = true;
        this.error = null;
        
        if (this.form.role === 'administrator') this.form.club_id = '';
        
        const isEdit = !!this.form.id;
        const url = isEdit ? `/api/users/${this.form.id}` : '/api/users';
        const method = isEdit ? 'PUT' : 'POST';
        
        try {
            const data = new FormData();
            if (isEdit) data.append('_method', 'PUT');
            
            data.append('name', this.form.name);
            data.append('email', this.form.email);
            data.append('phone', this.form.phone || '');
            data.append('role', this.form.role);
            if (this.form.club_id) data.append('club_id', this.form.club_id);
            data.append('is_active', this.form.is_active ? '1' : '0');
            
            this.form.team_ids.forEach(id => data.append('team_ids[]', id));
            this.form.squad_ids.forEach(id => data.append('squad_ids[]', id));
            this.form.child_ids.forEach(id => data.append('child_ids[]', id));
            
            if (this.form.password) data.append('password', this.form.password);
            if (this.form.photo) {
                if (this.form.photo instanceof Blob && !(this.form.photo instanceof File)) {
                    data.append('photo', this.form.photo, 'avatar.jpg');
                } else {
                    data.append('photo', this.form.photo);
                }
            }

            const res = await fetch(url, {
                method: isEdit ? 'POST' : 'POST', // Use POST for both, with _method if PUT
                headers: { 
                    'Accept': 'application/json', 
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}` 
                },
                body: data
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
