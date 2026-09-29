<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\PostManager;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(PostManager $posts): View
    {
        return view('public.posts.index', ['posts' => $posts->paginatePublic()]);
    }

    public function show(string $slug, PostManager $posts): View
    {
        return view('public.posts.show', ['post' => $posts->findPublicBySlug($slug)]);
    }
}
