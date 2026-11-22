<?php

namespace App\Policies;

use App\Models\SkpiCategory;
use App\Models\User;

class SkpiCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SkpiCategory $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'dosen']);
    }

    /**
     * Anti-IDOR Ownership Policy (Modul 6):
     * Admin may edit/delete any category; Dosen may only edit/delete categories they created.
     */
    public function update(User $user, SkpiCategory $category): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('dosen') && $category->created_by === $user->id);
    }

    public function delete(User $user, SkpiCategory $category): bool
    {
        return $this->update($user, $category);
    }
}
