<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryManager $categories) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Category::class);

        return view('admin.categories.index', ['categories' => $this->categories->paginateAdmin()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Category::class);

        return view('admin.categories.create', ['category' => new Category]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated());

        return to_route('admin.categories.index')->with('status', 'Categoría creada correctamente.');
    }

    public function edit(Category $category): View
    {
        Gate::authorize('update', $category);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categories->update($category, $request->validated());

        return to_route('admin.categories.index')->with('status', 'Categoría actualizada correctamente.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);
        $this->categories->setActive($category, ! $category->is_active);

        return to_route('admin.categories.index')->with('status', 'Estado de la categoría actualizado.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);
        $this->categories->delete($category);

        return to_route('admin.categories.index')->with('status', 'Categoría eliminada de forma lógica. El registro se conserva.');
    }
}
