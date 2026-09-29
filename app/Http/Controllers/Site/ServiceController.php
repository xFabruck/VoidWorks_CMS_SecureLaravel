<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\ServiceManager;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(ServiceManager $services): View
    {
        return view('public.services.index', ['services' => $services->active()]);
    }

    public function show(string $slug, ServiceManager $services): View
    {
        return view('public.services.show', ['service' => $services->findActiveBySlug($slug)]);
    }
}
