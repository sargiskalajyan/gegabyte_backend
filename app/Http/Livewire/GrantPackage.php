<?php

namespace App\Http\Livewire;

use App\Models\Package;
use App\Models\User;
use App\Services\Packages\UserPackageGrantService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class GrantPackage extends Component
{
    public string $search = '';

    public ?int $selectedUserId = null;

    public ?int $selectedPackageId = null;

    /**
     * @return void
     */
    public function chooseUser(int $userId): void
    {
        $user = User::find($userId);

        if (! $user) {
            $this->dispatch('show-admin-toast',
                title: __('packages.error_title'),
                message: __('packages.user_not_found'),
                icon: 'error'
            );

            return;
        }

        $this->selectedUserId = $user->id;

        $this->dispatch('show-admin-toast',
            title: __('packages.user_selected_title'),
            message: __('packages.user_selected_message', ['id' => $user->id]),
            icon: 'check'
        );
    }

    /**
     * @return void
     */
    public function grant(): void
    {
        if (! $this->selectedUserId || ! $this->selectedPackageId) {
            $this->dispatch('show-admin-toast',
                title: __('packages.error_title'),
                message: __('packages.validation_required'),
                icon: 'error'
            );

            return;
        }

        $user = User::find($this->selectedUserId);
        $package = Package::query()
            ->whereKey($this->selectedPackageId)
            ->where('is_active', true)
            ->first();

        if (! $user) {
            $this->dispatch('show-admin-toast',
                title: __('packages.error_title'),
                message: __('packages.user_not_found'),
                icon: 'error'
            );

            return;
        }

        if (! $package) {
            $this->dispatch('show-admin-toast',
                title: __('packages.error_title'),
                message: __('packages.package_not_found'),
                icon: 'error'
            );

            return;
        }

        try {
            $userPackage = app(UserPackageGrantService::class)->grant($user, $package);
        } catch (\Throwable $e) {
            Log::error('Admin package grant failed', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('show-admin-toast',
                title: __('packages.error_title'),
                message: __('packages.grant_failed'),
                icon: 'error'
            );

            return;
        }

        $this->dispatch('show-admin-toast',
            title: __('packages.success_title'),
            message: __('packages.success_message', [
                'user' => $user->username,
                'package' => $package->name,
            ]),
            icon: 'success'
        );

        $this->selectedPackageId = null;
        $this->search = '';
        $this->selectedUserId = $userPackage->user_id;
    }

    /**
     * @return array
     */
    public function render()
    {
        $search = trim($this->search);

        $users = collect();

        if ($search !== '') {
            $users = User::query()
                ->with('language')
                ->where(function ($query) use ($search) {
                    $escaped = addcslashes($search, "\\%_");
                    $like = "%{$escaped}%";

                    if (ctype_digit($search)) {
                        $query->orWhere('id', (int) $search);
                    }

                    $query->orWhere('username', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone_number', 'like', $like);
                })
                ->orderByDesc('id')
                ->limit(10)
                ->get();
        }

        $selectedUser = $this->selectedUserId
            ? User::with(['userPackages.package'])->find($this->selectedUserId)
            : null;

        $packages = Package::with('translations')
            ->where('is_active', true)
            ->orderBy('price')
            ->orderBy('id')
            ->get();

        return view('livewire.grant-package', compact('users', 'selectedUser', 'packages'));
    }
}