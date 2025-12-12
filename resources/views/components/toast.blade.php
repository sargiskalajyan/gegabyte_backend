<div
    class="toast fade hide p-2 bg-white"
    role="alert"
    aria-live="assertive"
    id="adminToast"
    aria-atomic="true"
>
    <div class="toast-header border-0">
        <i class="material-icons me-2 text-success" id="toastIcon">check</i>
        <span class="me-auto font-weight-bold" id="toastTitle">{{ __('listings.toast_title') }}</span>
        <small class="text-body" id="toastTime">{{ __('listings.just_now') }}</small>
        <i class="fas fa-times text-md ms-3 cursor-pointer" data-bs-dismiss="toast"></i>
    </div>
    <hr class="horizontal dark m-0">
    <div class="toast-body" id="toastBody">
        <!-- Dynamic content -->
    </div>
    <div id="toastImageWrapper" class="mt-2 d-none">
        <img id="toastImage" src="" class="img-fluid rounded" style="max-height:120px; object-fit:cover;">
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        window.Livewire.on('show-admin-toast', event => {
            const toastEl = document.getElementById('adminToast');
            const toast = new bootstrap.Toast(toastEl);

            document.getElementById('toastTitle').textContent = event.title;
            document.getElementById('toastBody').textContent = event.message;
            document.getElementById('toastIcon').textContent = event.icon ?? 'notifications';

            if (event.image) {
                document.getElementById('toastImageWrapper').classList.remove('d-none');
                document.getElementById('toastImage').src = event.image;
            } else {
                document.getElementById('toastImageWrapper').classList.add('d-none');
            }

            toast.show();
        });
    });


</script>
