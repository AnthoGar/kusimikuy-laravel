<?php

namespace App\Http\Controllers;

use App\Services\CalcularImpacto;

class HomeController extends Controller
{
    public function __construct(
        private CalcularImpacto $calcularImpacto
    ) {}

    public function index()
    {
        $impacto = $this->calcularImpacto->ejecutar();

        return view('pages.home', compact('impacto'));
    }
}
