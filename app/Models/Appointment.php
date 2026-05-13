<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointments';

    protected $primaryKey = 'appointment_id';

    protected $fillable = [
        'patient_no',
        'appointment_date',
        'appointment_time',
        'status',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_no');
    }
}