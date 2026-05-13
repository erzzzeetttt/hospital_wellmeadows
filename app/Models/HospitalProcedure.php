<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalProcedure extends Model
{
    protected $table = 'hospital_procedures';

    protected $primaryKey = 'procedure_id';

    protected $fillable = [
        'patient_no',
        'procedure_name',
        'description',
        'procedure_date',
        'cost',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_no', 'patient_no');
    }
}