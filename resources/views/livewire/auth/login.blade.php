<div class="container my-auto">
    <div class="row">
        <div class="col-lg-4 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">
                <div class="card-body">
                    <form wire:submit='store'>
                        @if (Session::has('status'))
                            <div class="alert alert-success alert-dismissible text-white" role="alert">
                                <span class="text-sm">{{ Session::get('status') }}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10"
                                        data-bs-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="input-group input-group-outline mt-3 @if(strlen($email ?? '') > 0) is-filled @endif">
                            <label class="form-label">{{ __('auth.email') }}</label>
                            <input wire:model.live='email' type="email" class="form-control">
                        </div>
                        @error('email') <p class="text-danger inputerror">{{ $message }}</p> @enderror

                        <div class="input-group input-group-outline mt-3 @if(strlen($password ?? '') > 0) is-filled @endif">
                            <label class="form-label">{{ __('auth.password') }}</label>
                            <input wire:model.live="password" type="password" class="form-control">
                        </div>
                        @error('password') <p class="text-danger inputerror">{{ $message }}</p> @enderror

                        <div class="form-check form-switch d-flex align-items-center my-3">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label mb-0 ms-2" for="rememberMe">
                                {{ __('auth.remember_me') }}
                            </label>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">
                                {{ __('auth.sign_in') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
