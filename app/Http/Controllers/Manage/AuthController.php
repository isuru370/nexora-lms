<?php

namespace App\Http\Controllers\Manage;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PhoneLoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private LoginService $login) {}

    public function create(): View
    {
        return view('manage.auth.login');
    }

    public function store(PhoneLoginRequest $request): RedirectResponse
    {
        $this->login->attempt(
            $request,
            'phone',
            $request->validated('phone'),
            $request->validated('password'),
            [Role::Teacher, Role::Assistant],
            $request->boolean('remember'),
        );

        return redirect()->intended(route('manage.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->login->logout($request);

        return redirect()->route('manage.login');
    }
}
