<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function index()
    {
        return view('marcas');
    }
}
