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
    locale: document.documentElement.lang || 'ro-RO',
    showDetailsModal: false,
    selectedLogData: null,
    statusLabels: {
        'active_paid': '{{ __('subscriptions.status.paid') }}',
        'active_pending': '{{ __('subscriptions.status.pending') }}',
        'active_overdue': '{{ __('subscriptions.status.overdue') }}',
        'expired': '{{ __('subscriptions.status.expired') }}',
        'inactive_expired': '{{ __('subscriptions.status.expired') }}',
        'cancelled': '{{ __('subscriptions.status.cancelled') }}',
        'scheduled': '{{ __('subscriptions.status.scheduled') }}',
        'ACTIVE_PAID': '{{ __('subscriptions.status.paid') }}',
        'ACTIVE_PENDING': '{{ __('subscriptions.status.pending') }}'
    },

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
            'name': '{{ __('audit.keys.name') }}',
            'email': '{{ __('audit.keys.email') }}',
            'role': '{{ __('audit.keys.role') }}',
            'is_active': '{{ __('audit.keys.is_active') }}',
            'club_id': '{{ __('audit.keys.club_id') }}',
            'team_ids': '{{ __('audit.keys.team_ids') }}',
            'squad_ids': '{{ __('audit.keys.squad_ids') }}',
            'status': '{{ __('audit.keys.status') }}',
            'price': '{{ __('audit.keys.price') }}',
            'period': '{{ __('audit.keys.period') }}',
            'address': '{{ __('audit.keys.address') }}',
            'day_of_week': '{{ __('audit.keys.day_of_week') }}',
            'start_time': '{{ __('audit.keys.start_time') }}',
            'end_time': '{{ __('audit.keys.end_time') }}',
            'location_id': '{{ __('audit.keys.location_id') }}',
            'coach_id': '{{ __('audit.keys.coach_id') }}',
            'team_id': '{{ __('audit.keys.team_id') }}',
            'starts_at': '{{ __('audit.keys.starts_at') }}',
            'expires_at': '{{ __('audit.keys.expires_at') }}',
            'subscription_id': '{{ __('audit.keys.subscription_id') }}',
            'previous': '{{ __('admin.previous') }}',
            'next': '{{ __('admin.next') }}'
        };
        return keys[key] || key;
    },

    translateAuditValue(key, val) {
        if (val === null || val === undefined || val === '') return '{{ __('admin.not_available') }}';
        if (val === true || val === '1' || val === 1) return '{{ __('admin.yes') }}';
        if (val === false || val === '0' || val === 0) return '{{ __('admin.no') }}';
        
        if (key === 'status') return this.statusLabels[val] || val;
        if (key === 'role') {
            const roles = { 
                'administrator': '{{ __('members.roles_filter.admin') }}', 
                'manager': '{{ __('members.roles_filter.manager') }}', 
                'antrenor': '{{ __('members.roles_filter.coach') }}', 
                'sportiv': '{{ __('members.roles_filter.student') }}' 
            };
            return roles[val] || val;
        }
        if (key === 'day_of_week') {
            const days = { 
                'luni': '{{ __('trainings.form.days.luni') }}', 
                'marti': '{{ __('trainings.form.days.marti') }}', 
                'miercuri': '{{ __('trainings.form.days.miercuri') }}', 
                'joi': '{{ __('trainings.form.days.joi') }}', 
                'vineri': '{{ __('trainings.form.days.vineri') }}', 
                'sambata': '{{ __('trainings.form.days.sambata') }}', 
                'duminica': '{{ __('trainings.form.days.duminica') }}' 
            };
            return days[val] || val;
        }
        if (key === 'period') {
            const periods = { 
                '1_saptamana': '{{ __('subscriptions.form.periods.1_saptamana') }}', 
                '2_saptamani': '{{ __('subscriptions.form.periods.2_saptamani') }}', 
                '1_luna': '{{ __('subscriptions.form.periods.1_luna') }}', 
                '3_luni': '{{ __('subscriptions.form.periods.3_luni') }}', 
                '6_luni': '{{ __('subscriptions.form.periods.6_luni') }}', 
                '1_an': '{{ __('subscriptions.form.periods.1_an') }}' 
            };
            return periods[val] || val;
        }

        return val;
    },

    translateAuditType(type) {
        const cleanType = type.split('\\').pop();
        const types = {
            'User': '{{ __('audit.types.User') }}',
            'Club': '{{ __('audit.types.Club') }}',
            'Subscription': '{{ __('audit.types.Subscription') }}',
            'UserSubscription': '{{ __('audit.types.UserSubscription') }}',
            'Team': '{{ __('audit.types.Team') }}',
            'Squad': '{{ __('audit.types.Squad') }}',
            'Location': '{{ __('audit.types.Location') }}',
            'Training': '{{ __('audit.types.Training') }}'
        };
        return types[cleanType] || cleanType;
    }
}));
