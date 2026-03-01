<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TagService
{
    /**
     * Get all tags for a user.
     */
    public function getForUser(User $user): Collection
    {
        return $user->tags()->orderBy('name')->get();
    }

    /**
     * Sync tags for an expense. Creates new tags as needed.
     * Accepts an array of tag names (strings).
     */
    public function syncForExpense(User $user, $expense, array $tagNames): void
    {
        $tagIds = [];

        foreach ($tagNames as $name) {
            $name = trim($name);
            if (empty($name)) continue;

            $tag = Tag::firstOrCreate(
                ['user_id' => $user->id, 'name' => strtolower($name)],
            );
            $tagIds[] = $tag->id;
        }

        $expense->tags()->sync($tagIds);
    }

    /**
     * Get tag suggestions for autocomplete (returns matching tags).
     */
    public function search(User $user, string $query): Collection
    {
        return $user->tags()
            ->where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get();
    }
}
