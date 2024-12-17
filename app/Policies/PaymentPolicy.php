<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
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
    public function view(User $user, Payment $payment): bool
    {
        // Admin can view all appointments payments
        if ($user->hasRole('admin')) {
            return true;
        }

        // Doctor can view their own appointments payments
        // if ($user->hasRole('doctor') && $user->id === $payment->appointment->doctor->user->id) {
        //     return true;
        // }

        // Patient can view their own appointments payments
        if ($user->hasRole('patient') && $user->id === $payment->appointment->patient->user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('patient');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payment $payment): bool
    {
                // Admin can update all appointments payments
                if ($user->hasRole('admin')) {
                    return true;
                }

                // Patient can update their own appointments payments
                if ($user->hasRole('patient') && $user->id === $payment->appointment->patient->user->id) {
                    return true;
                }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payment $payment): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Payment $payment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Payment $payment): bool
    {
        return false;
    }
}
