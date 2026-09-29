<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\BannerService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(BannerService $banners): View
    {
        return view('public.home', ['banners' => $banners->visible(), 'isPreview' => false]);
    }
}
