Alpine.data('homeManager', () => ({
    stats: {
        kpi: {},
        trends: {},
        recent_clubs: [],
        recent_members: [],
        recent_subscriptions: [],
        recent_conversations: [],
    },
    loading: false,

    init() {
        this.$watch('currentPage', (val) => {
            if (val === '/dash') this.loadStats();
        });
        this.$watch('user', (user) => {
            if (user && this.currentPage === '/dash') this.loadStats();
        });
        if (this.currentPage === '/dash' && this.user) {
            this.loadStats();
        }
    },

    async loadStats() {
        if (this.loading) return;
        this.loading = true;
        try {
            const res = await fetch('/api/dashboard-stats', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                }
            });
            if (res.ok) {
                const payload = await res.json();
                this.stats = payload.data;
            } else {
                console.error('Dashboard stats error:', res.status);
            }
        } catch (e) {
            console.error('Dashboard stats fetch failed:', e);
        }
        this.loading = false;
    },

    // Returns a percentage height (5–100) for a bar in the chart
    barHeightPct(trend, idx) {
        if (!trend || trend.length === 0) return 10;
        const max = Math.max(...trend, 1);
        const val = trend[idx] ?? 0;
        return Math.max(Math.round((val / max) * 100), val > 0 ? 5 : 3);
    },

    // Translates English DB status values to Romanian
    statusLabel(status) {
        const map = {
            'ACTIVE_PAID': 'Activ',
            'ACTIVE':      'Activ',
            'activ':       'Activ',
            'CANCELLED':   'Anulat',
            'cancelled':   'Anulat',
            'anulat':      'Anulat',
            'EXPIRED':     'Expirat',
            'INACTIVE':    'Inactiv',
            'expirat':     'Expirat',
            'PENDING':     'Așteptare',
            'pending':     'Așteptare',
        };
        return map[status] || status;
    },

    isActiv(status) {
        return ['ACTIVE_PAID', 'ACTIVE', 'activ'].includes(status);
    },
}));
