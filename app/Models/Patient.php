<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $table = 'patients';

    protected $primaryKey = 'patient_no';

    protected $fillable = [
        'doctor_id',
        'nextofkinid',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'address',
        'phone_no',
        'marital_status',
    ];

    public function doctor()
    {
        return $this->belongsTo(LocalDoctor::class, 'doctor_id');
    }

    public function nextOfKin()
    {
        return $this->belongsTo(NextOfKin::class, 'nextofkinid');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_no');
    }
}