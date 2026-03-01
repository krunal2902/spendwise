<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;

class CategoryService
{
    /**
     * Create a new custom category for the user.
     */
    public function create(User $user, array $data): Category
    {
        $data['is_system'] = false;
        return $user->categories()->create($data);
    }

    /**
     * Update a category (only non-system categories).
     *
     * @throws \Exception
     */
    public function update(Category $category, array $data): Category
    {
        if ($category->is_system) {
            throw new \Exception('System categories cannot be modified.');
        }

        $category->update($data);
        return $category->fresh();
    }

    /**
     * Toggle category active status.
     */
    public function toggle(Category $category): Category
    {
        $category->update(['is_active' => !$category->is_active]);
        return $category->fresh();
    }

    /**
     * Get all categories visible to a user (system + custom), optionally filtered by type.
     */
    public function getForUser(User $user, ?string $type = null)
    {
        $query = Category::forUser($user->id)->active();

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('is_system', 'desc')->orderBy('name')->get();
    }
}
