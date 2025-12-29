<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class UsersTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public function render()
    {
        // Fetch users with counts
        $users = User::withCount([
            // Active listings: published & not expired
            'listings as active_listings_count' => function ($q) {
                $q->where('status', 'published');
//                    ->where('published_until', '>=', now());
            },
            // Total listings (all statuses)
            'listings as total_listings_count'
        ])
            ->latest()
            ->paginate(10);

        return view('livewire.users-table', compact('users'));
    }
}
