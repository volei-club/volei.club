<!-- Global Cropper Modal -->
<div x-data="cropperManager" 
     x-show="show" 
     style="display: none;" 
     class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4"
     @keyup.escape.window="cancel()"
     @click.stop>
    
    <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Ajustează Poza</h3>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mt-1">Decupează imaginea pentru un aspect optim</p>
            </div>
            <button @click="cancel()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-500 transition-all">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="flex-1 bg-slate-100 dark:bg-black p-4 flex items-center justify-center overflow-hidden min-h-[300px]">
            <div class="max-w-full max-h-full">
                <img x-ref="cropperImage" src="" class="max-w-full opacity-0">
            </div>
        </div>

        <!-- Controls -->
        <div class="p-6 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <button @click="cropper.rotate(-90)" class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-primary hover:text-primary transition-all shadow-sm" title="Rotește la stânga">
                    <span class="material-symbols-outlined text-[20px]">rotate_left</span>
                </button>
                <button @click="cropper.rotate(90)" class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-primary hover:text-primary transition-all shadow-sm" title="Rotește la dreapta">
                    <span class="material-symbols-outlined text-[20px]">rotate_right</span>
                </button>
                <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 mx-1"></div>
                <button @click="cropper.zoom(0.1)" class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-primary hover:text-primary transition-all shadow-sm" title="Mărește">
                    <span class="material-symbols-outlined text-[20px]">zoom_in</span>
                </button>
                <button @click="cropper.zoom(-0.1)" class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-primary hover:text-primary transition-all shadow-sm" title="Micșorează">
                    <span class="material-symbols-outlined text-[20px]">zoom_out</span>
                </button>
            </div>

            <div class="flex gap-3 ml-auto">
                <button @click="cancel()" class="px-6 py-3 rounded-2xl font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                    Anulează
                </button>
                <button @click="save()" class="px-8 py-3 rounded-2xl font-bold bg-primary hover:bg-primary-dark text-white shadow-lg shadow-primary/20 transition-all flex items-center">
                    <span class="material-symbols-outlined mr-2">check_circle</span>
                    Confirmă Decuparea
                </button>
            </div>
        </div>
    </div>
</div>
