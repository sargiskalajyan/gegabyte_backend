<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;

class OrdersTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public function render()
    {
        $orders = Order::with(['user', 'package', 'advertisement'])
            ->latest()
            ->paginate(10);

        return view('livewire.orders-table', compact('orders'));
    }
}
