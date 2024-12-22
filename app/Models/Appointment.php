<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_date',
        'slot',
        'status',
    ];
    public function doctor(){
        return $this->belongsTo(Doctor::class);
    }

    public function patient(){
        return $this->belongsTo(Patient::class);
    }

    public function payment(){
        return $this->hasOne(Payment::class);
    }

    public function message(){
        return $this->hasOne(Message::class);
    }

}
