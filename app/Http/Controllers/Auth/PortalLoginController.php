<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalLoginController extends Controller
{
    /**
     * Show the login form for a specific portal (admin, subadmin).
     * Library/user login is handled by Fortify at /login.
     */
    public function show(Request $request): Response
    {
        $portal = match ($request->route()->getName()) {
            'admin.login' => 'admin',
            'subadmin.login' => 'subadmin',
            default => null,
        };

        if ($portal === null) {
            abort(404);
        }

        return Inertia::render('auth/AdminLogin');
    }
}
