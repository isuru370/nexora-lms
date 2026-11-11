<?php

namespace App\Http\Controllers\Student;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PhoneLoginRequest;
use App\Http\Requests\Student\RegisterRequest;
use App\Services\Auth\LoginService;
use App\Services\Students\RegisterStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private LoginService $login) {}

    public function create(): View
    {
        return view('student.auth.login');
    }

    public function store(PhoneLoginRequest $request): RedirectResponse
    {
        $this->login->attempt(
            $request,
            'phone',
            $request->validated('phone'),
            $request->validated('password'),
            [Role::Student],
            $request->boolean('remember'),
        );

        return redirect()->intended(route('student.dashboard'));
    }

    public function showRegister(): View
    {
        return view('student.auth.register');
    }

    public function register(RegisterRequest $request, RegisterStudent $registerStudent): RedirectResponse
    {
        $user = $registerStudent->handle($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('student.dashboard')
            ->with('status', __('Welcome, :name! Your account is ready.', ['name' => $user->name]));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->login->logout($request);

        return redirect()->route('tenant.home');
    }
}
