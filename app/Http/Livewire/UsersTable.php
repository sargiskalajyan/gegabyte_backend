<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class UsersTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = User::query();

        $search = trim($this->search);
        if ($search !== '') {
            $escaped = addcslashes($search, "\\%_");
            $like = "%{$escaped}%";

            $query->where(function ($q) use ($search, $like) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }

                $q->orWhere('username', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone_number', 'like', $like);
            });
        }

        // Fetch users with counts
        $users = $query->withCount([
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
