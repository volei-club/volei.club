Alpine.data('cropperManager', () => ({
    show: false,
    cropper: null,
    callback: null,
    currentFile: null,

    init() {
        window.addEventListener('open-cropper', (e) => {
            this.open(e.detail.file, e.detail.callback);
        });
    },

    open(file, callback) {
        if (!file) return;
        this.currentFile = file;
        this.callback = callback;
        this.show = true;

        const reader = new FileReader();
        reader.onload = (e) => {
            const image = this.$refs.cropperImage;
            
            // Set onload BEFORE src to avoid missing cached events
            image.onload = () => {
                image.classList.remove('opacity-0');
                if (this.cropper) {
                    this.cropper.destroy();
                }

                this.cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 1,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
            };
            image.src = e.target.result;
        };
        reader.readAsDataURL(file);
    },

    cancel() {
        this.show = false;
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }
    },

    save() {
        if (!this.cropper) return;

        // Resize to 400x400 for profile pictures
        const canvas = this.cropper.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        if (!canvas) {
            alert('Eroare la procesarea imaginii');
            return;
        }

        canvas.toBlob((blob) => {
            if (!blob) {
                alert('Eroare la generarea fișierului');
                return;
            }

            if (this.callback) {
                this.callback(blob, canvas.toDataURL('image/jpeg'));
            }
            this.cancel();
        }, 'image/jpeg', 0.9);
    }
}));
