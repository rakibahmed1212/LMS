<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class PublicPageController extends Controller
{
    public function about()
    {
        return Inertia::render('Public/About');
    }

    public function contact()
    {
        return Inertia::render('Public/Contact');
    }

    public function faq()
    {
        return Inertia::render('Public/Faq');
    }

    public function privacy()
    {
        return Inertia::render('Public/Privacy');
    }

    public function terms()
    {
        return Inertia::render('Public/Terms');
    }
}
