<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DonanteDashboardController extends Controller
{
    public function index()
    {
        return view('dashboards.donante.index');
    }
}