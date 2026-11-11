<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('student.dashboard', [
            'user' => $request->user(),
            'student' => $request->user()->student,
        ]);
    }
}
