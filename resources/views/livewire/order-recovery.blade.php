<div class="container-fluid py-4">
    <div class="position-fixed top-1 end-1 z-index-2">
        @include('components.toast')
    </div>

    <div class="card mb-4">
        <div class="card-header bg-gradient-primary text-white">
            <h6 class="mb-0 text-white">{{ __('orders.recovery_title') }}</h6>
        </div>

        <div class="card-body">
            <p class="text-muted mb-4">
                {{ __('orders.recovery_description') }}
            </p>

            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label">{{ __('orders.recovery_search_label') }}</label>
                    <input type="text"
                           class="form-control"
                           wire:model.defer="search"
                           placeholder="{{ __('orders.recovery_search_placeholder') }}">
                </div>

                <div class="col-12 col-md-auto">
                    <button class="btn btn-primary mb-0"
                            type="button"
                            wire:click="findOrder"
                            wire:loading.attr="disabled"
                            wire:target="findOrder">
                        {{ __('orders.recovery_search') }}
                    </button>
                </div>

                <div class="col-12 col-md-auto">
                    @if($order)
                        <button class="btn btn-success mb-0"
                                type="button"
                                wire:click="recoverOrder"
                                wire:loading.attr="disabled"
                                wire:target="recoverOrder">
                            {{ __('orders.recovery_activate') }}
                        </button>
                    @endif
                </div>
            </div>

            @if($order)
                <hr class="horizontal dark my-4">

                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <div class="bg-gray-100 border-radius-lg p-3 h-100">
                            <div class="text-xs text-uppercase text-muted">{{ __('orders.id') }}</div>
                            <div class="fw-bold">#{{ $order->id }}</div>

                            <div class="mt-3 text-xs text-uppercase text-muted">{{ __('orders.status') }}</div>
                            <div class="fw-bold">{{ __('orders.status_' . $order->status) }}</div>

                            <div class="mt-3 text-xs text-uppercase text-muted">{{ __('orders.gateway') }}</div>
                            <div class="fw-bold">{{ strtoupper($order->gateway ?? '-') }}</div>

                            <div class="mt-3 text-xs text-uppercase text-muted">{{ __('orders.amount') }}</div>
                            <div class="fw-bold">{{ number_format((float) $order->amount, 0) }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="bg-gray-100 border-radius-lg p-3 h-100">
                            <div class="text-xs text-uppercase text-muted">{{ __('orders.user') }}</div>
                            <div class="fw-bold">{{ $order->user->username ?? '-' }}</div>
                            <div class="text-sm text-muted">{{ $order->user->email ?? '' }}</div>

                            <div class="mt-3 text-xs text-uppercase text-muted">{{ __('orders.package') }}</div>
                            <div class="fw-bold">{{ $order->package->name ?? '-' }}</div>

                            <div class="mt-3 text-xs text-uppercase text-muted">{{ __('orders.advertisement') }}</div>
                            <div class="fw-bold">{{ $order->advertisement?->name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="bg-gray-100 border-radius-lg p-3 h-100">
                            <div class="text-xs text-uppercase text-muted">{{ __('orders.reference') }}</div>
                            <div class="fw-bold">{{ $order->reference ?? '-' }}</div>

                            <div class="mt-3 text-xs text-uppercase text-muted">{{ __('orders.created_at') }}</div>
                            <div class="fw-bold">{{ $order->created_at?->format('Y-m-d H:i') }}</div>

                            <div class="mt-3 text-xs text-uppercase text-muted">{{ __('orders.description') }}</div>
                            <div class="fw-bold">{{ $order->description ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="bg-gray-100 border-radius-lg p-3">
                            <div class="text-xs text-uppercase text-muted mb-2">{{ __('orders.payload') }}</div>
                            <pre class="mb-0 text-sm" style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($order->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>