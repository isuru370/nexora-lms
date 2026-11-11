<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Tenancy\CurrentTeacher;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(CurrentTeacher $current): View
    {
        return view('tenant.landing', ['teacher' => $current->require()]);
    }
}
