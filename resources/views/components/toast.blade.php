<div
    class="toast fade hide p-2 bg-white shadow rounded"
    role="alert"
    aria-live="assertive"
    id="adminToast"
    aria-atomic="true"
>
    <div class="toast-header border-0 align-items-center" id="toastHeader">
        <i class="fas fa-circle-info me-2 d-none" id="toastIcon"></i>
        <span class="me-auto fw-bold" id="toastTitle">{{ __('listings.toast_title') }}</span>
        <small class="text-muted" id="toastTime">{{ __('listings.just_now') }}</small>
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
            const toastHeader = document.getElementById('toastHeader');
            const toastIcon = document.getElementById('toastIcon');

            toastHeader.className = 'toast-header border-0 align-items-center';
            toastIcon.className = 'fas me-2';

            const severity = (event.icon || 'info').toLowerCase();
            const severityMap = {
                success: { header: 'bg-success text-white', icon: 'fa-circle-check' },
                check: { header: 'bg-success text-white', icon: 'fa-circle-check' },
                error: { header: 'bg-danger text-white', icon: 'fa-circle-xmark' },
                danger: { header: 'bg-danger text-white', icon: 'fa-circle-xmark' },
                warning: { header: 'bg-warning text-dark', icon: 'fa-triangle-exclamation' },
                info: { header: 'bg-info text-white', icon: 'fa-circle-info' },
            };

            const variant = severityMap[severity] || severityMap.info;
            toastHeader.classList.add(...variant.header.split(' '));
            toastIcon.classList.add(variant.icon);
            toastIcon.classList.remove('d-none');

            document.getElementById('toastTitle').textContent = event.title;
            document.getElementById('toastBody').textContent = event.message;

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
