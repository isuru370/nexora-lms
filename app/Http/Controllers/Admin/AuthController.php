<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailLoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private LoginService $login) {}

    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(EmailLoginRequest $request): RedirectResponse
    {
        $this->login->attempt(
            $request,
            'email',
            $request->validated('email'),
            $request->validated('password'),
            [Role::SuperAdmin],
            $request->boolean('remember'),
        );

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->login->logout($request);

        return redirect()->route('admin.login');
    }
}
