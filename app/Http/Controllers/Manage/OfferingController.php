<?php

namespace App\Http\Controllers\Manage;

use App\Enums\EnrolmentStatus;
use App\Enums\OfferingType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\OfferingRequest;
use App\Models\Offering;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OfferingController extends Controller
{
    public function index(): View
    {
        $offerings = Offering::query()
            ->withCount([
                'lessons',
                'enrolments as students_count' => fn ($q) => $q->where('status', EnrolmentStatus::Active->value),
            ])
            ->orderBy('subject')->orderBy('sort')->orderByDesc('id')
            ->get()
            ->groupBy('subject');

        return view('manage.classes.index', compact('offerings'));
    }

    public function create(): View
    {
        return view('manage.classes.form', [
            'offering' => new Offering([
                'type' => OfferingType::MonthlyClass,
                'old_lessons_access' => 'forever',
                'past_lessons_access' => false,
                'grace_days' => 0,
                'is_published' => true,
            ]),
            'subjects' => $this->subjects(),
        ]);
    }

    public function store(OfferingRequest $request): RedirectResponse
    {
        $data = $this->normalise($request->validated(), OfferingType::from($request->validated('type')));
        $data['slug'] = Offering::uniqueSlug($data['title']);
        $data['sort'] = (int) Offering::query()->max('sort') + 1;

        $offering = Offering::create($data);

        AuditLogger::log('offering.created', $offering, ['title' => $offering->title]);

        return redirect()->route('manage.classes.show', $offering)
            ->with('status', __(':title is ready. Now add your first lessons.', ['title' => $offering->title]));
    }

    public function show(Offering $offering): View
    {
        $lessons = $offering->lessons()->ordered()->get();

        return view('manage.classes.show', [
            'offering' => $offering,
            'lessonGroups' => $offering->isClass() ? $lessons->groupBy('month') : collect(['' => $lessons]),
            'lessonsCount' => $lessons->count(),
            'studentsCount' => $offering->enrolments()->where('status', EnrolmentStatus::Active->value)->count(),
        ]);
    }

    public function edit(Offering $offering): View
    {
        return view('manage.classes.form', ['offering' => $offering, 'subjects' => $this->subjects()]);
    }

    public function update(OfferingRequest $request, Offering $offering): RedirectResponse
    {
        $data = $this->normalise($request->validated(), $offering->type);

        if ($data['title'] !== $offering->title) {
            $data['slug'] = Offering::uniqueSlug($data['title'], $offering->id);
        }

        $offering->update($data);

        AuditLogger::log('offering.updated', $offering, array_keys($offering->getChanges()));

        return redirect()->route('manage.classes.show', $offering)->with('status', __('Saved.'));
    }

    public function destroy(Offering $offering): RedirectResponse
    {
        if ($offering->enrolments()->exists()) {
            return back()->withErrors(['offering' => __('Students have joined this class, so it cannot be deleted. Unpublish it instead.')]);
        }

        $offering->delete();

        AuditLogger::log('offering.deleted', $offering, ['title' => $offering->title]);

        return redirect()->route('manage.classes.index')->with('status', __(':title was deleted.', ['title' => $offering->title]));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalise(array $data, OfferingType $type): array
    {
        unset($data['type']);
        $data['type'] = $type;

        if ($type === OfferingType::Course) {
            // Month-based settings do not apply to courses.
            $data['past_lessons_access'] = false;
            $data['grace_days'] = 0;
        } else {
            $data['access_days'] = null;
        }

        return $data;
    }

    /**
     * @return list<string>
     */
    private function subjects(): array
    {
        return Offering::query()->distinct()->orderBy('subject')->pluck('subject')->all();
    }
}
