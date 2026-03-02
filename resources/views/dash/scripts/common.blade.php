// Global Helper for Toasts
window.showToast = (message, type = 'success') => {
    window.dispatchEvent(new CustomEvent('notify', { detail: { message, type } }));
};
