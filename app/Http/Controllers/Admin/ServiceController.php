<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Service;
use App\Services\ServiceManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(private readonly ServiceManager $services) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Service::class);

        return view('admin.services.index', ['services' => $this->services->paginateAdmin()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Service::class);

        return view('admin.services.create', ['service' => new Service]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $this->services->create($request->validated(), (int) $request->user()->getKey());

        return to_route('admin.services.index')->with('status', 'Servicio creado correctamente.');
    }

    public function edit(Service $service): View
    {
        Gate::authorize('update', $service);

        return view('admin.services.edit', compact('service'));
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $this->services->update($service, $request->validated());

        return to_route('admin.services.index')->with('status', 'Servicio actualizado correctamente.');
    }

    public function toggle(Service $service): RedirectResponse
    {
        Gate::authorize('update', $service);
        $this->services->setActive($service, ! $service->is_active);

        return to_route('admin.services.index')->with('status', 'Estado del servicio actualizado.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        Gate::authorize('delete', $service);
        $this->services->delete($service);

        return to_route('admin.services.index')->with('status', 'Servicio eliminado correctamente.');
    }
}
