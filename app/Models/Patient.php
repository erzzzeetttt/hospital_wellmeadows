<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'status',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(LocalDoctor::class, 'doctor_id');
    }

    public function nextOfKin(): BelongsTo
    {
        return $this->belongsTo(NextOfKin::class, 'nextofkinid');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_no');
    }

    public function wardAdmissions(): HasMany
    {
        return $this->hasMany(WardAdmission::class, 'patient_no', 'patient_no');
    }

    public function activeWardAllocation(): HasOne
    {
        return $this->hasOne(WardAllocation::class, 'patient_no', 'patient_no')->whereNull('release_date');
    }
}
