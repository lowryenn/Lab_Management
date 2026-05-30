<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin()
    {
        return view('dashboard.admin');
    }

    public function kepalaLab()
    {
        return view('dashboard.kepala_lab');
    }

    public function kaprodi()
    {
        return view('dashboard.kaprodi');
    }

    public function staffAdmin()
    {
        return view('dashboard.staff_admin');
    }

    public function staffLab()
    {
        return view('dashboard.staff_lab');
    }
}
