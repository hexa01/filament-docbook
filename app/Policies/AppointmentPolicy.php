<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AppointmentPolicy
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
    public function view(User $user, Appointment $appointment): bool
    {
              // Admin can view all appointments
              if ($user->hasRole('admin')) {
                return true;
            }

            // Doctor can view their own appointments
            if ($user->hasRole('doctor') && $user->id === $appointment->doctor->user->id) {
                return true;
            }

            // Patient can view their own appointments
            if ($user->hasRole('patient') && $user->id === $appointment->patient->user->id) {
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
    public function update(User $user, Appointment $appointment): bool
    {
                     // Admin can update all appointments
                     if ($user->hasRole('admin')) {
                        return true;
                    }

                    // Doctor can update their own appointments
                    // if ($user->hasRole('doctor') && $user->id === $appointment->doctor->user->id) {
                    //     return true;
                    // }

                    // Patient can update their own appointments
                    if ($user->hasRole('patient') && $user->id === $appointment->patient->user->id) {
                        return true;
                    }
                    return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
                      // Admin can view all appointments
                      if ($user->hasRole('admin')) {
                        return true;
                    }

                    // Patient can delete their own appointments
                    if ($user->hasRole('patient') && $user->id === $appointment->patient->user->id) {
                        return true;
                    }
                    return false;
    }


    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Appointment $appointment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return false;
    }
}
