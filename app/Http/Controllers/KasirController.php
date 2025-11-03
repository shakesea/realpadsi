<?php

namespace App\Http\Controllers;

use App\Models\Menu;

class KasirController extends Controller
{
    public function index()
    {
        $menus = Menu::orderBy('Kategori')->get();
        return view('kasir', compact('menus'));
    }
}
