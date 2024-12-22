<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Specialization extends Model
{
        /** @use HasFactory<\Database\Factories\SpecializationFactory> */
        use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function doctors(){
        return $this->hasMany(Doctor::class);
    }
}
