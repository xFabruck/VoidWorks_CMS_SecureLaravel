<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryManager
{
    public function paginateAdmin(): LengthAwarePaginator
    {
        return Category::query()->orderBy('name')->paginate(15);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->refresh();
    }

    public function setActive(Category $category, bool $active): Category
    {
        $category->update(['is_active' => $active]);

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        // SoftDeletes preserves the row for future publication references.
        $category->delete();
    }
}
