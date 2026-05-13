<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $table = 'patients';

    protected $primaryKey = 'patient_no';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'patient_no',
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
        return $this->hasMany(Appointment::class, 'patient_no', 'patient_no');
    }

    public function wardAdmissions()
    {
        return $this->hasMany(WardAdmission::class, 'patient_no', 'patient_no');
    }

    public function medicationRecords()
    {
        return $this->hasMany(MedicationRecord::class, 'patient_no', 'patient_no');
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class, 'patient_no', 'patient_no');
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class, 'patient_no', 'patient_no');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'patient_no', 'patient_no');
    }

    public function wardAllocations()
    {
        return $this->hasMany(WardAllocation::class, 'patient_no', 'patient_no');
    }
}