<div class="container-fluid py-4">

    {{-- Toasts --}}
    <div class="position-fixed top-1 end-1 z-index-2">
        @include('components.toast')
    </div>

    <div class="card my-4">
        <div class="card-header bg-gradient-primary text-white">
            <h6 class="mb-0 text-white">{{ __('orders.title') }}</h6>
        </div>

        <div class="card-body px-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0 text-center">
                    <thead>
                    <tr>
                        <th>{{ __('orders.id') }}</th>
                        <th>{{ __('orders.user') }}</th>
                        <th>{{ __('orders.package') }}</th>
                        <th>{{ __('orders.amount') }}</th>
                        <th>{{ __('orders.gateway') }}</th>
                        <th>{{ __('orders.status') }}</th>
                        <th>{{ __('orders.description') }}</th>
                        <th>{{ __('orders.created_at') }}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>

                            <td>
                                {{ $order->user->username ?? '-' }} <br>
                                <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                            </td>

                            <td>
                                {{ $order->package->name ?? '-' }}
                            </td>

                            <td>
                                    <span class="fw-bold">
                                        {{ number_format($order->amount) }}
                                    </span>
                            </td>

                            <td>
                                    <span class="badge bg-gradient-secondary">
                                        {{ strtoupper($order->gateway) }}
                                    </span>
                            </td>

                            <td>
                                @php
                                    $statusColors = [
                                        'pending'  => 'warning',
                                        'paid'     => 'success',
                                        'failed'   => 'danger',
                                        'refunded' => 'info',
                                    ];
                                @endphp

                                <span class="badge bg-gradient-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                        {{ __('orders.status_' . $order->status) }}
                                </span>
                            </td>

                            <td>
                                {{ $order->description }}
                            </td>

                            <td>
                                {{ $order->created_at->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                {{ __('orders.no_orders') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
