<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\VideoManager;
use Illuminate\View\View;

class VideosController extends Controller
{
    public function __construct(private readonly VideoManager $videos) {}

    public function index(): View
    {
        return view('public.videos.index', ['videos' => $this->videos->active()]);
    }
}
