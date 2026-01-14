<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContratoController extends Controller
{
    public function variaveis()
    {
        return view('contrato.variaveis');
    }
}