<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WardAdmission extends Model
{
    protected $table = 'ward_admissions';

    protected $primaryKey = 'admission_id';

    protected $fillable = [
        'patient_no',
        'date_admitted',
        'expected_leave_date',
        'discharge_date',
        'status',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_no', 'patient_no');
    }
}
