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
        $teacher = $createTeacher->handle($request->validated());

        return redirect()
            ->route('admin.teachers.index')
            ->with('status', __(':name is ready at :url', [
                'name' => $teacher->display_name,
                'url' => $teacher->url(),
            ]));
    }
}
