<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MessagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('doctor') || $user->hasRole('patient');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Message $message): bool
    {
        // Admin can view all messages
        if ($user->hasRole('admin')) {
            return true;
        }

        // Doctor can view their own messages
        if ($user->hasRole('doctor') && $user->id === $message->appointment->doctor->user->id) {
            return true;
        }

        // Patient can view their own messages
        if ($user->hasRole('patient') && $user->id === $message->appointment->patient->user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('doctor');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Message $message): bool
    {
         // Admin can update all messages
        if ($user->hasRole('admin')) {
            return true;
        }

        // Doctor can update their own messages
        if ($user->hasRole('doctor') && $user->id === $message->appointment->doctor->user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Message $message): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Message $message): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Message $message): bool
    {
        return false;
    }
}
