<div class="container-fluid py-4">

    {{-- Toasts always top right --}}
    <div class="position-fixed top-1 end-1 z-index-2">
        @include('components.toast')
    </div>

    <div class="card my-4">
        <div class="card-header bg-gradient-primary text-white">
            <h6 class="mb-0 text-white">{{ __('users.title') }}</h6>
        </div>

        <div class="card-body px-0">

            <div class="table-responsive">
                <table class="table align-items-center mb-0 text-center">
                    <thead>
                    <tr>
                        <th>{{ __('users.id') }}</th>
                        <th>{{ __('users.user') }}</th>
                        <th>{{ __('users.email') }}</th>
                        <th>{{ __('users.phone') }}</th>
                        <th>{{ __('users.active_listings') }}</th>
                        <th>{{ __('users.total_listings') }}</th>
                        <th>{{ __('users.actions') }}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone_number ?? '-' }}</td>
                            <td>
                                <span class="badge bg-gradient-info">{{ $user->active_listings_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-gradient-secondary">{{ $user->total_listings_count  }}</span>
                            </td>
                            <td>
                                <a
                                    href="{{ route('users.impersonate', $user->id) }}"
                                    class="btn btn-sm btn-outline-primary"
                                    target="_blank"
                                >
                                    {{ __('users.login_as') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">{{ __('users.no_users') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
