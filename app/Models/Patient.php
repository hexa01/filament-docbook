<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }


    public function appointments(){
        return $this->hasMany(Appointment::class);
    }

    protected static function booted()
    {
        static::deleting(function (Patient $patient) {
            if ($patient->user) {
                $patient->user->delete();  // Delete the related user
            }
        });
    }
}
