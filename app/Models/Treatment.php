<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    protected $table = 'treatments';

    protected $primaryKey = 'treatment_id';

    protected $fillable = [
        'patient_no',
        'treatment_details',
        'treatment_date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_no');
    }

    public function treatmentStaff()
    {
        return $this->hasMany(TreatmentStaff::class, 'treatment_id');
    }
}