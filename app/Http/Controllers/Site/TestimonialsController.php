<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\TestimonialManager;
use Illuminate\View\View;

class TestimonialsController extends Controller
{
    public function __construct(private readonly TestimonialManager $testimonials) {}

    public function index(): View
    {
        return view('public.testimonials.index', ['testimonials' => $this->testimonials->active()]);
    }
}
