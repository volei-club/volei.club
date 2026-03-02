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
