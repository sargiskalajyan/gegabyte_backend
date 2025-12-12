<div wire:poll.10s="loadStats">
    <div class="container-fluid py-4">
        <div class="row">

            <!-- Total Users -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">group</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0">{{ __('dashboard.total_users') }}</p>
                            <h4 class="mb-0">{{ $totalUsers }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Listings -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">home</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0">{{ __('dashboard.total_listings') }}</p>
                            <h4 class="mb-0">{{ $totalListings }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Users Today -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">person_add</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0">{{ __('dashboard.new_users_today') }}</p>
                            <h4 class="mb-0">{{ $newUsersToday }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Listings Today -->
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">add_home</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0">{{ __('dashboard.new_listings_today') }}</p>
                            <h4 class="mb-0">{{ $newListingsToday }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
