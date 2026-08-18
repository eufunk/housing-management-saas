<?php

namespace App\Http\Controllers\Auth;

use App\Actions\ProvisionDemoAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    /**
     * Log the visitor in as the shared public demo account, provisioning it
     * first if this is the very first demo login (or it was deleted since).
     */
    public function store(Request $request, ProvisionDemoAccount $provisionDemoAccount): RedirectResponse
    {
        $user = $provisionDemoAccount->handle();

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
