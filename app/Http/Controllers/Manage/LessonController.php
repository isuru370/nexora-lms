<?php

namespace App\Http\Controllers\Manage;

use App\Enums\LessonVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\StoreLessonsRequest;
use App\Http\Requests\Manage\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\Offering;
use App\Support\AuditLogger;
use App\Support\Month;
use App\Support\YouTube;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function create(Offering $offering): View
    {
        return view('manage.lessons.create', [
            'offering' => $offering,
            'defaultMonth' => Month::current(),
        ]);
    }

    public function store(StoreLessonsRequest $request, Offering $offering): RedirectResponse
    {
        $ids = $request->videoIds();
        $sort = (int) $offering->lessons()->max('sort');
        $published = $request->boolean('is_published');
        $created = 0;

        foreach ($ids as $index => $videoId) {
            $offering->lessons()->create([
                'title' => YouTube::title($videoId) ?? __('Lesson :n', ['n' => $sort + 1]),
                'month' => $offering->isClass() ? $request->validated('month') : null,
                'video_provider' => 'youtube',
                'video_ref' => $videoId,
                'visibility' => $request->validated('visibility'),
                'available_until' => $request->validated('available_until'),
                'is_published' => $published,
                'published_at' => $published ? now() : null,
                'sort' => ++$sort,
            ]);
            $created++;
        }

        AuditLogger::log('lessons.created', $offering, ['count' => $created]);

        return redirect()->route('manage.classes.show', $offering)
            ->with('status', trans_choice('{1} 1 lesson added.|[2,*] :count lessons added.', $created, ['count' => $created]));
    }

    public function edit(Offering $offering, Lesson $lesson): View
    {
        return view('manage.lessons.edit', compact('offering', 'lesson'));
    }

    public function update(UpdateLessonRequest $request, Offering $offering, Lesson $lesson): RedirectResponse
    {
        $published = $request->boolean('is_published');

        $lesson->update([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'video_ref' => $request->videoId(),
            'month' => $offering->isClass() ? $request->validated('month') : null,
            'visibility' => $request->validated('visibility'),
            'available_until' => $request->validated('available_until'),
            'duration_minutes' => $request->validated('duration_minutes'),
            'is_published' => $published,
            'published_at' => $published ? ($lesson->published_at ?? now()) : null,
        ]);

        AuditLogger::log('lesson.updated', $lesson, array_keys($lesson->getChanges()));

        return redirect()->route('manage.classes.show', $offering)->with('status', __('Lesson saved.'));
    }

    public function destroy(Offering $offering, Lesson $lesson): RedirectResponse
    {
        $lesson->delete();

        AuditLogger::log('lesson.deleted', $lesson, ['title' => $lesson->title]);

        return redirect()->route('manage.classes.show', $offering)->with('status', __('Lesson deleted.'));
    }

    /**
     * Live preview while the teacher pastes links: id, title, thumbnail.
     */
    public function lookup(Request $request): JsonResponse
    {
        $id = YouTube::parseId($request->query('url'));

        if ($id === null) {
            return response()->json(['ok' => false], 422);
        }

        return response()->json([
            'ok' => true,
            'id' => $id,
            'title' => YouTube::title($id),
            'thumbnail' => YouTube::thumbnail($id),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function visibilityOptions(): array
    {
        return collect(LessonVisibility::cases())->mapWithKeys(fn ($v) => [$v->value => $v->label()])->all();
    }
}
