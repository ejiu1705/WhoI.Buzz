<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function view(User $user, Activity $activity): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('mahasiswa')) {
            return $activity->student_id === $user->id;
        }

        if ($user->hasRole('dosen')) {
            $activity->loadMissing('student.studentProfile');

            return $activity->student?->studentProfile?->advisor_id === $user->id;
        }

        return false;
    }

    public function update(User $user, Activity $activity): bool
    {
        if ($activity->isLocked()) {
            return false;
        }

        return $user->hasRole('mahasiswa')
            && $activity->student_id === $user->id
            && in_array($activity->status, ['draft', 'revision'], true);
    }

    public function toggleVisibility(User $user, Activity $activity): bool
    {
        return $user->hasRole('mahasiswa') && $activity->student_id === $user->id;
    }

    public function review(User $user, Activity $activity): bool
    {
        if ($activity->isLocked()) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        $activity->loadMissing('student.studentProfile');

        return $user->hasRole('dosen')
            && $activity->student?->studentProfile?->advisor_id === $user->id;
    }
}
