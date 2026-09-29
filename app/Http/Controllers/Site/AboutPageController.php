<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\AboutPageManager;
use Illuminate\View\View;

class AboutPageController extends Controller
{
    public function __invoke(AboutPageManager $aboutPages): View
    {
        return view('public.about', ['aboutPage' => $aboutPages->active()]);
    }
}
