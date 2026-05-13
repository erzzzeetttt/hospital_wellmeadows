<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationRecord extends Model
{
    protected $primaryKey = 'medication_id';

    protected $fillable = [
        'patient_no',
        'drug_id',
        'dosage',
        'frequency',
        'start_date',
        'end_date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_no', 'patient_no');
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class, 'drug_id');
    }
}