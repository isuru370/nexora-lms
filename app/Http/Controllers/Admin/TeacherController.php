<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Models\Teacher;
use App\Services\Teachers\CreateTeacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $teachers = Teacher::query()
            ->withCount(['users as students_count' => fn ($query) => $query->where('role', Role::Student->value)])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('display_name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.teachers.index', compact('teachers', 'search'));
    }

    public function create(): View
    {
        return view('admin.teachers.create');
    }

    public function store(StoreTeacherRequest $request, CreateTeacher $createTeacher): RedirectResponse
    {
        $teacher = $createTeacher->handle($request->validated(), sendInvite: false);
        $email = $request->validated('email');

        try {
            $createTeacher->sendInvite($teacher);
            $message = __(':name is ready at :url. Login details were sent to :email.', [
                'name' => $teacher->display_name,
                'url' => $teacher->url(),
                'email' => $email,
            ]);
        } catch (Throwable $e) {
            report($e);
            $message = __(':name is ready at :url, but the email could not be sent. Check the mail settings, then use "Resend invite".', [
                'name' => $teacher->display_name,
                'url' => $teacher->url(),
            ]);
        }

        return redirect()->route('admin.teachers.index')->with('status', $message);
    }

    public function invite(Teacher $teacher, CreateTeacher $createTeacher): RedirectResponse
    {
        try {
            $createTeacher->sendInvite($teacher);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['invite' => __('The email could not be sent. Check the mail settings in .env.')]);
        }

        return back()->with('status', __('A new login link was sent to :email.', [
            'email' => $teacher->owner()->value('email'),
        ]));
    }
}
