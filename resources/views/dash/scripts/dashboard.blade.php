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
