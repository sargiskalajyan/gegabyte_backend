<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Profile extends Component
{
    public $old_password;
    public $password;
    public $password_confirmation;

    public function rules()
    {
        return [
            'old_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function changePassword()
    {
        $this->validate();

        $admin = Auth::guard('admin')->user();

        if (!Hash::check($this->old_password, $admin->password)) {
            $this->addError('old_password', __('profile.password_incorrect'));
            return;
        }

        $admin->password = Hash::make($this->password);
        $admin->save();

        // reset fields
        $this->old_password = $this->password = $this->password_confirmation = '';

        session()->flash('status', __('profile.password_updated'));
    }

    public function render()
    {
        return view('livewire.profile');
    }
}
