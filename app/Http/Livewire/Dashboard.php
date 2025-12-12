<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Listing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $totalUsers;
    public $totalListings;
    public $newUsersToday;
    public $newListingsToday;

    public $usersMonthly = [];
    public $listingsMonthly = [];
    public $months = [];

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        // Main numbers
        $this->totalUsers        = User::count();
        $this->totalListings     = Listing::count();
        $this->newUsersToday     = User::whereDate('created_at', today())->count();
        $this->newListingsToday  = Listing::whereDate('created_at', today())->count();

        // Last 12 months chart
        $data = collect(range(11, 0))->map(function ($i) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            return [
                'month' => $month,
                'users' => User::whereYear('created_at', substr($month, 0, 4))
                    ->whereMonth('created_at', substr($month, 5, 2))
                    ->count(),
                'listings' => Listing::whereYear('created_at', substr($month, 0, 4))
                    ->whereMonth('created_at', substr($month, 5, 2))
                    ->count(),
            ];
        });

        $this->months          = $data->pluck('month')->map(fn($m) => Carbon::parse($m)->format('M'))->toArray();
        $this->usersMonthly    = $data->pluck('users')->toArray();
        $this->listingsMonthly = $data->pluck('listings')->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
