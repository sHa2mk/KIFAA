<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Logs the current user out of the application.
     *
     * The method logs out the authenticated web user, invalidates the current
     * session, regenerates the CSRF token, and redirects the user to the home
     * page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke()
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect('/');
    }
}