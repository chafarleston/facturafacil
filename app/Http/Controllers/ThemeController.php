<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ThemeController extends Controller
{
    public function change(Request $request)
    {
        $theme = $request->input('theme', 'blue');
        
        Session::put('theme', $theme);
        
        return response()->json(['success' => true, 'theme' => $theme]);
    }
    
    public function getTheme()
    {
        return Session::get('theme', 'blue');
    }
}