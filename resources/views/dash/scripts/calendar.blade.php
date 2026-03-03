Alpine.data('calendarManager', () => ({
    loading: false,
    sessions: [],
    currentWeekStart: null,

    // Attendance modal (coach)
    showAttendanceModal: false,
    attendanceSession: null,
    attendanceMembers: [],
    loadingAttendance: false,
    savingAttendance: {},

    // Parent child selector
    children: [],
    selectedChildId: null,

    async init() {
        const today = new Date();
        this.currentWeekStart = this.getMonday(today);
        await this.loadChildren();
        await this.fetchSessions();
        
        // Listen for refresh events
        window.addEventListener('game-saved', () => this.fetchSessions());
        window.addEventListener('refresh-calendar', () => this.fetchSessions());
    },

    async loadChildren() {
        if (this.user?.role !== 'parinte') return;
        try {
            const res = await fetch(`/api/users?per_page=100`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if (res.ok) {
                const data = await res.json();
                // Filter to children only
                const childIds = (this.user.children_ids ?? []);
                this.children = (data.data ?? []).filter(u => u.role === 'sportiv');
                if (this.children.length > 0) this.selectedChildId = this.children[0].id;
            }
        } catch(e) { console.error(e); }
    },

    async fetchSessions() {
        this.loading = true;
        try {
            const params = new URLSearchParams({ weeks: 8 });
            if (this.user?.role === 'parinte' && this.selectedChildId) {
                params.append('child_id', this.selectedChildId);
            }
            const res = await fetch(`/api/my-calendar?${params}`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if (res.ok) {
                const data = await res.json();
                this.sessions = data.data || [];
            }
        } catch(e) { console.error(e); }
        this.loading = false;
    },

    async fetchAvailableSquads() {
        try {
            const res = await fetch('/api/squads', {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if (res.ok) {
                const data = await res.json();
                this.availableSquads = data.data || data;
            }
        } catch(e) {}
    },

    // --- Week helpers ---
    getMonday(d) {
        const date = new Date(d);
        const day = date.getDay();
        const diff = (day === 0 ? -6 : 1 - day);
        date.setDate(date.getDate() + diff);
        date.setHours(0,0,0,0);
        return date;
    },

    prevWeek() {
        const d = new Date(this.currentWeekStart);
        d.setDate(d.getDate() - 7);
        this.currentWeekStart = d;
    },

    nextWeek() {
        const d = new Date(this.currentWeekStart);
        d.setDate(d.getDate() + 7);
        this.currentWeekStart = d;
    },

    get weekDays() {
        const days = [];
        const dayNames = ['Luni', 'Marți', 'Miercuri', 'Joi', 'Vineri', 'Sâmbătă', 'Duminică'];
        for (let i = 0; i < 7; i++) {
            const d = new Date(this.currentWeekStart);
            d.setDate(d.getDate() + i);
            
            // Format to YYYY-MM-DD in local time to avoid UTC shift
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const dayNum = String(d.getDate()).padStart(2, '0');
            const dateStr = `${year}-${month}-${dayNum}`;

            days.push({
                label: dayNames[i],
                date: dateStr,
                dateObj: d,
                isToday: d.toDateString() === new Date().toDateString(),
            });
        }
        return days;
    },

    get weekLabel() {
        const end = new Date(this.currentWeekStart);
        end.setDate(end.getDate() + 6);
        const fmt = (d) => d.toLocaleDateString('ro-RO', { day: '2-digit', month: 'short' });
        return `${fmt(this.currentWeekStart)} – ${fmt(end)} ${end.getFullYear()}`;
    },

    sessionsForDay(dateStr) {
        return this.sessions.filter(s => s.date === dateStr);
    },

    statusColor(session) {
        if (session.type === 'game') return 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-400';
        
        const status = session.status;
        if (status === 'prezent') return 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400';
        if (status === 'absent') return 'bg-red-50 dark:bg-red-900/30 border-red-300 dark:border-red-700 text-red-700 dark:text-red-400';
        if (status === 'motivat') return 'bg-amber-50 dark:bg-amber-900/30 border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-400';
        return 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300';
    },

    statusIcon(status) {
        if (status === 'prezent') return 'check_circle';
        if (status === 'absent') return 'cancel';
        if (status === 'motivat') return 'info';
        return 'radio_button_unchecked';
    },

    statusLabel(status) {
        if (status === 'prezent') return 'Prezent';
        if (status === 'absent') return 'Absent';
        if (status === 'motivat') return 'Motivat';
        return 'Neînregistrat';
    },

    canMarkAttendance() {
        return ['antrenor', 'manager', 'administrator'].includes(this.user?.role);
    },

    // --- Attendance Modal ---
    async openAttendance(session) {
        this.attendanceSession = session;
        this.showAttendanceModal = true;
        this.loadingAttendance = true;
        try {
            const res = await fetch(`/api/attendances?training_id=${session.training_id}&date=${session.date}`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            if (res.ok) {
                const data = await res.json();
                this.attendanceMembers = data.data || [];
            }
        } catch(e) { console.error(e); }
        this.loadingAttendance = false;
    },

    closeAttendance() {
        this.showAttendanceModal = false;
        this.attendanceSession = null;
        this.attendanceMembers = [];
    },

    async markAttendance(member, status) {
        this.savingAttendance = { ...this.savingAttendance, [member.user_id]: true };
        try {
            const res = await fetch('/api/attendances', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                },
                body: JSON.stringify({
                    training_id: this.attendanceSession.training_id,
                    user_id: member.user_id,
                    date: this.attendanceSession.date,
                    status: status,
                    notes: member.notes ?? null,
                })
            });
            if (res.ok) {
                const data = await res.json();
                const idx = this.attendanceMembers.findIndex(m => m.user_id === member.user_id);
                if (idx !== -1) {
                    this.attendanceMembers[idx] = { ...this.attendanceMembers[idx], status, attendance_id: data.data.id };
                }
            }
        } catch(e) { console.error(e); }
        this.savingAttendance = { ...this.savingAttendance, [member.user_id]: false };
    },

    openGameModal(game = null) {
        Alpine.store('gameModal').open(game);
    },
}));
