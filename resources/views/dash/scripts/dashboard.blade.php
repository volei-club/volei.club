// Initialize global auth store early
if (!Alpine.store('auth')) {
    Alpine.store('auth', { user: null });
}

Alpine.data('dashboard', () => ({
    user: null,
    isLoading: true,
    token: null,
    isMobileMenuOpen: false,
    isImpersonating: false,
    currentPage: window.location.pathname, // Route Tracker Simplu
    unreadMessagesCount: 0,

        getPageTitle() {
            if(this.currentPage === '/dash') return 'Acasă';
            if(this.currentPage.startsWith('/dash/cluburi')) return 'Management Cluburi';
            if(this.currentPage.startsWith('/dash/membri')) return 'Membri';
            if(this.currentPage.startsWith('/dash/echipe')) return 'Echipe';
            if(this.currentPage.startsWith('/dash/abonamente')) return 'Abonamente';
            if(this.currentPage.startsWith('/dash/calendar')) return 'Calendar';
            if(this.currentPage.startsWith('/dash/meciuri')) return 'Meciuri';
            if(this.currentPage.startsWith('/dash/performanta')) return 'Performanță';
            return 'Dashboard';
        },

    navigate(path) {
        const calendarRoles = ['antrenor', 'sportiv', 'parinte'];
        if (this.user) {
            const calendarRoles = ['antrenor', 'sportiv', 'parinte'];
            const isCalendarOrPerf = path.startsWith('/dash/calendar') || 
                                    path.startsWith('/dash/performanta') || 
                                    path.startsWith('/dash/meciuri') || 
                                    path.startsWith('/dash/abonamente');
            
            if (!['administrator', 'manager'].includes(this.user.role)
                && path !== '/dash'
                && !path.startsWith('/dash/mesaje')
                && !(calendarRoles.includes(this.user.role) && isCalendarOrPerf)
            ) {
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

        // Listen for unread count refresh events
        window.addEventListener('refresh-unread-count', () => {
            this.fetchUnreadCount();
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

        // Configure global Axios headers
        if (window.axios) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
            axios.defaults.headers.common['Accept'] = 'application/json';
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
                
                // Publish to global store for access from other stores (like gameModal)
                if (!Alpine.store('auth')) {
                    Alpine.store('auth', { user: null });
                }
                Alpine.store('auth').user = this.user;

                this.isLoading = false;
                
                // Security check fallback for deep links
                const calendarRoles = ['antrenor', 'sportiv', 'parinte'];
                if (!['administrator', 'manager'].includes(this.user.role)
                    && this.currentPage !== '/dash'
                    && !this.currentPage.startsWith('/dash/mesaje')
                    && !(calendarRoles.includes(this.user.role) && (
                        this.currentPage.startsWith('/dash/calendar') || 
                        this.currentPage.startsWith('/dash/performanta') ||
                        this.currentPage.startsWith('/dash/meciuri') ||
                        this.currentPage.startsWith('/dash/abonamente')
                    ))
                ) {
                    this.navigate('/dash/calendar');
                } else if (this.user.role === 'manager' && this.currentPage.startsWith('/dash/cluburi')) {
                    this.navigate('/dash');
                }

                this.fetchUnreadCount();
                // Poll for unread count every 30 seconds
                setInterval(() => this.fetchUnreadCount(), 30000);
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
    },

    async fetchUnreadCount() {
        if (!this.token) return;
        try {
            const response = await axios.get('/api/chat/unread-count');
            if (response.data.status === 'success') {
                this.unreadMessagesCount = response.data.unread_count;
            }
        } catch (error) {
            console.error('Error fetching unread count:', error);
        }
    }
}));
