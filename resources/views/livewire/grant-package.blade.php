<div class="container-fluid py-4">
    <div class="position-fixed top-1 end-1 z-index-2">
        @include('components.toast')
    </div>

    <div class="card mb-4">
        <div class="card-header bg-gradient-primary text-white">
            <h6 class="mb-0 text-white">{{ __('packages.title') }}</h6>
        </div>

        <div class="card-body">
            <p class="text-muted mb-4">
                {{ __('packages.description') }}
            </p>

            <div class="row g-3 align-items-end mb-4">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-outline @if(strlen($search ?? '') > 0) is-filled @endif">
                        <label class="form-label">{{ __('packages.search_label') }}</label>
                        <input type="text"
                               class="form-control"
                               wire:model.live.debounce.300ms="search"
                               autocomplete="off">
                    </div>
                </div>

                <div class="col-12 col-md-auto">
                    @if(!empty($search))
                        <button class="btn btn-outline-secondary mb-0"
                                type="button"
                                wire:click="$set('search','')"
                                wire:loading.attr="disabled"
                                wire:target="search">
                            {{ __('packages.clear_search') }}
                        </button>
                    @endif
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-3">{{ __('packages.users_found') }}</h6>

                        @if(trim($search) === '')
                            <div class="text-muted">{{ __('packages.search_prompt') }}</div>
                        @else
                            @forelse($users as $user)
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary w-100 text-start mb-2"
                                        wire:click="chooseUser({{ $user->id }})">
                                    <strong>#{{ $user->id }}</strong> {{ $user->username }}
                                    <div class="small text-muted">{{ $user->email }}</div>
                                </button>
                            @empty
                                <div class="text-muted">{{ __('packages.no_users_found') }}</div>
                            @endforelse
                        @endif
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-3">{{ __('packages.selection_title') }}</h6>

                        <div class="mb-3">
                            <label class="form-label">{{ __('packages.selected_user') }}</label>
                            <div class="form-control bg-gray-100">
                                {{ $selectedUser ? '#'.$selectedUser->id.' '.$selectedUser->username : __('packages.no_user_selected') }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('packages.package_label') }}</label>
                            <select class="form-control" wire:model.defer="selectedPackageId">
                                <option value="">{{ __('packages.select_package') }}</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}">
                                        #{{ $package->id }} - {{ $package->name }}
                                        ({{ number_format((float) $package->price, 0) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if($selectedUser)
                            <div class="mb-3 bg-gray-100 rounded p-3">
                                <div class="text-xs text-uppercase text-muted">{{ __('packages.current_package') }}</div>
                                <div class="fw-bold">
                                    {{ $selectedUser->activePackage()?->package?->name ?? __('packages.no_active_package') }}
                                </div>
                            </div>
                        @endif

                        <button class="btn btn-success mb-0"
                                type="button"
                                wire:click="grant"
                                wire:loading.attr="disabled"
                                wire:target="grant">
                            {{ __('packages.grant_button') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>