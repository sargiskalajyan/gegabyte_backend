<div class="container-fluid px-2 px-md-4">
    <div class="page-header min-height-300 border-radius-xl mt-4"
         style="background-image: url('https://images.unsplash.com/photo-1531512073830-ba890ca4eba2?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');">
        <span class="mask  bg-gradient-primary  opacity-6"></span>
    </div>
    <div class="card-body p-3">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible text-white" role="alert">
                <span class="text-sm">{{ session('status') }}</span>
                <button type="button" class="btn-close text-lg opacity-10" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <form wire:submit.prevent='changePassword'>
            <div class="row">
                <div class="mb-3 col-md-6">
                    <label class="form-label">{{ __('profile.old_password') }}</label>
                    <input wire:model.blur="old_password" type="password" class="form-control border border-2 p-2">
                    @error('old_password') <p class='text-danger inputerror'>{{ $message }}</p> @enderror
                </div>
                <div class="mb-3 col-md-6">
                    <label class="form-label">{{ __('profile.password') }}</label>
                    <input wire:model.blur="password" type="password" class="form-control border border-2 p-2">
                    @error('password') <p class='text-danger inputerror'>{{ $message }}</p> @enderror
                </div>
                <div class="mb-3 col-md-6">
                    <label class="form-label">{{ __('profile.password_confirmation') }}</label>
                    <input wire:model.blur="password_confirmation" type="password" class="form-control border border-2 p-2">
                </div>
            </div>
            <button type="submit" class="btn bg-gradient-dark">{{ __('profile.submit') }}</button>
        </form>
    </div>
</div>
