<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTestimonialRequest;
use App\Http\Requests\Admin\UpdateTestimonialRequest;
use App\Models\Testimonial;
use App\Services\TestimonialManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function __construct(private readonly TestimonialManager $testimonials) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Testimonial::class);

        return view('admin.testimonials.index', ['testimonials' => $this->testimonials->paginateAdmin()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Testimonial::class);

        return view('admin.testimonials.create', ['testimonial' => new Testimonial]);
    }

    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $this->testimonials->create($request->validated());

        return to_route('admin.testimonials.index')->with('status', 'Testimonio creado correctamente.');
    }

    public function edit(Testimonial $testimonial): View
    {
        Gate::authorize('update', $testimonial);

        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $this->testimonials->update($testimonial, $request->validated());

        return to_route('admin.testimonials.index')->with('status', 'Testimonio actualizado correctamente.');
    }

    public function toggle(Testimonial $testimonial): RedirectResponse
    {
        Gate::authorize('update', $testimonial);
        $this->testimonials->setActive($testimonial, ! $testimonial->is_active);

        return to_route('admin.testimonials.index')->with('status', 'Estado del testimonio actualizado.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        Gate::authorize('delete', $testimonial);
        $this->testimonials->delete($testimonial);

        return to_route('admin.testimonials.index')->with('status', 'Testimonio eliminado correctamente.');
    }
}
