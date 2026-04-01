<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->dashboardRoute());
    }

    public function student(): View
    {
        return view('dashboards.student');
    }

    public function staff(): View
    {
        return view('dashboards.staff');
    }

    public function admin(): View
    {
        return view('dashboards.admin');
    }
}
