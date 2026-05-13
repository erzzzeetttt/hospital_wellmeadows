<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    protected $table = 'diagnoses';

    protected $primaryKey = 'diagnosis_id';

    protected $fillable = [
        'patient_no',
        'staff_no',
        'diagnosis_details',
        'diagnosis_date',
    ];

    public function patient()
    {
       return $this->belongsTo(Patient::class, 'patient_no', 'patient_no');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_no');
    }
}