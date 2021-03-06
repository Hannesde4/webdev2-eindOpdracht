<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{

    public function about()
    {
        return view('pages/about');
    }

    public function contact()
    {
        return view('pages/contact');
    }

    public function privacy()
    {
        return view('pages/privacy');
    }

    public function projects()
    {
        return view('projects');
    }
}
