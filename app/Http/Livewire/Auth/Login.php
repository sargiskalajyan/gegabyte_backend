<?php

namespace App\Http\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{

    /**
     * @var string
     */
    public $email='';

    /**
     * @var string
     */
    public $password='';


    /**
     * @var string[]
     */
    protected $rules= [
        'email' => 'required|email',
        'password' => 'required'

    ];


    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function render()
    {
        return view('livewire.auth.login');
    }


    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws ValidationException
     */
    public function store()
    {
        $attributes = $this->validate();
        if (Auth::guard('admin')->attempt($attributes)) {
            session()->regenerate();
            return redirect('/dashboard');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.invalid_credentials')
        ]);

    }
}
