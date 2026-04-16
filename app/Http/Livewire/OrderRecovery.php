<?php

namespace App\Http\Livewire;

use App\Models\Order;
use App\Services\Payments\AmeriaService;
use App\Services\Payments\OrderActivationService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class OrderRecovery extends Component
{
    public string $search = '';

    public ?int $selectedOrderId = null;

    /**
     * @return void
     */
    public function findOrder(): void
    {
        $search = trim($this->search);

        if ($search === '') {
            $this->dispatch('show-admin-toast',
                title: __('orders.recovery_error_title'),
                message: __('orders.recovery_empty_search'),
                icon: 'error'
            );

            return;
        }

        $order = Order::with(['user', 'package', 'advertisement'])
            ->where('id', $search)
            ->orWhere('reference', $search)
            ->first();

        if (! $order) {
            $this->selectedOrderId = null;

            $this->dispatch('show-admin-toast',
                title: __('orders.recovery_error_title'),
                message: __('orders.recovery_not_found'),
                icon: 'error'
            );

            return;
        }

        $this->selectedOrderId = $order->id;

        $this->dispatch('show-admin-toast',
            title: __('orders.recovery_found_title'),
            message: __('orders.recovery_found_message', ['id' => $order->id]),
            icon: 'check'
        );
    }

    /**
     * @return void
     */
    public function recoverOrder(): void
    {
        if (! $this->selectedOrderId) {
            return;
        }

        $order = Order::with(['user', 'package', 'advertisement'])
            ->find($this->selectedOrderId);

        if (! $order) {
            $this->dispatch('show-admin-toast',
                title: __('orders.recovery_error_title'),
                message: __('orders.recovery_not_found'),
                icon: 'error'
            );

            return;
        }

        if ($order->status === 'paid') {
            $this->dispatch('show-admin-toast',
                title: __('orders.recovery_info_title'),
                message: __('orders.recovery_already_paid', ['id' => $order->id]),
                icon: 'info'
            );

            return;
        }

        if (! $order->reference) {
            $this->dispatch('show-admin-toast',
                title: __('orders.recovery_error_title'),
                message: __('orders.recovery_missing_reference', ['id' => $order->id]),
                icon: 'error'
            );

            return;
        }

        try {
            $ameria = new AmeriaService();
            $details = $ameria->getPaymentDetails($order->reference);
        } catch (\Throwable $e) {
            Log::error('Order recovery failed while loading payment details', [
                'order_id' => $order->id,
                'reference' => $order->reference,
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('show-admin-toast',
                title: __('orders.recovery_error_title'),
                message: __('orders.recovery_gateway_error'),
                icon: 'error'
            );

            return;
        }

        if (($details['ResponseCode'] ?? null) !== '00' || (int) ($details['OrderStatus'] ?? 0) !== 2) {
            $this->dispatch('show-admin-toast',
                title: __('orders.recovery_error_title'),
                message: __('orders.recovery_not_completed'),
                icon: 'warning'
            );

            return;
        }

        $payload = $order->payload ?? [];
        $opaqueData = $this->decodeOpaque($details['Opaque'] ?? null);

        if (! isset($payload['listing_id']) && isset($opaqueData['listing_id'])) {
            $payload['listing_id'] = $opaqueData['listing_id'];
        }

        if (! isset($payload['advertisement_id']) && isset($opaqueData['advertisement_id'])) {
            $payload['advertisement_id'] = $opaqueData['advertisement_id'];
        }

        $payload['ameria_details'] = $details;

        try {
            $order = app(OrderActivationService::class)->activate($order, $payload, $order->reference);
        } catch (\Throwable $e) {
            Log::error('Order recovery activation failed', [
                'order_id' => $order->id,
                'reference' => $order->reference,
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('show-admin-toast',
                title: __('orders.recovery_error_title'),
                message: __('orders.recovery_activation_failed'),
                icon: 'error'
            );

            return;
        }

        $this->selectedOrderId = $order->id;

        $this->dispatch('show-admin-toast',
            title: __('orders.recovery_success_title'),
            message: __('orders.recovery_success_message', ['id' => $order->id]),
            icon: 'check'
        );
    }

    /**
     * @param string|null $opaque
     * @return array
     */
    protected function decodeOpaque(?string $opaque): array
    {
        if (empty($opaque)) {
            return [];
        }

        $decoded = json_decode($opaque, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded)
            ? $decoded
            : [];
    }

    /**
     * @return array
     */
    public function render()
    {
        $order = $this->selectedOrderId
            ? Order::with(['user', 'package', 'advertisement'])->find($this->selectedOrderId)
            : null;

        return view('livewire.order-recovery', compact('order'));
    }
}