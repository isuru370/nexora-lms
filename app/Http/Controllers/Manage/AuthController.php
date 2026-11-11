<?php

namespace App\Http\Controllers\Manage;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PanelLoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private LoginService $login) {}

    public function create(): View
    {
        return view('manage.auth.login');
    }

    public function store(PanelLoginRequest $request): RedirectResponse
    {
        [$field, $value] = $request->credential();

        try {
            $this->login->attempt(
                $request,
                $field,
                $value,
                $request->validated('password'),
                [Role::Teacher, Role::Assistant],
                $request->boolean('remember'),
            );
        } catch (ValidationException $e) {
            // Show the message under the single "Email or mobile" box.
            throw ValidationException::withMessages(['login' => $e->errors()[$field] ?? [__('These details do not match our records.')]]);
        }

        return redirect()->intended(route('manage.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->login->logout($request);

        return redirect()->route('manage.login');
    }
}
