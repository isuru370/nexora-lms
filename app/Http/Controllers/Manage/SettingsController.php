<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\UpdateSettingsRequest;
use App\Services\Teachers\TeacherMedia;
use App\Support\AuditLogger;
use App\Tenancy\CurrentTeacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(CurrentTeacher $current): View
    {
        return view('manage.settings', ['teacher' => $current->require()]);
    }

    public function update(UpdateSettingsRequest $request, CurrentTeacher $current, TeacherMedia $media): RedirectResponse
    {
        $teacher = $current->require();

        $teacher->update($request->safe()->only([
            'display_name', 'subject_line', 'bio', 'whatsapp', 'brand_color',
        ]));

        if ($request->hasFile('photo')) {
            $media->storePhoto($teacher, $request->file('photo'));
        } elseif ($request->boolean('remove_photo')) {
            $media->removePhoto($teacher);
        }

        if ($request->hasFile('logo')) {
            $media->storeLogo($teacher, $request->file('logo'));
        } elseif ($request->boolean('remove_logo')) {
            $media->removeLogo($teacher);
        }

        AuditLogger::log('teacher.settings_updated', $teacher, array_keys($teacher->getChanges()));

        return redirect()->route('manage.settings')->with('status', __('Settings saved.'));
    }
}
