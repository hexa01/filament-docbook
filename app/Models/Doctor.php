<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function specialization(){
        return $this->belongsTo(Specialization::class);
    }

    public function appointments(){
        return $this->hasMany(Appointment::class);
    }

    public function schedules(){
        return $this->hasMany(Schedule::class);
    }

    protected static function booted()
    {
        static::deleting(function (Doctor $doctor) {
            if ($doctor->user) {
                $doctor->user->delete();  // Delete the related user
            }
        });
    }

}
