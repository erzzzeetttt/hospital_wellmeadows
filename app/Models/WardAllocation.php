<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WardAllocation extends Model
{
    protected $table = 'ward_allocations';

    protected $primaryKey = 'allocation_id';

    protected $fillable = [
        'patient_no',
        'ward_id',
        'bed_id',
        'allocation_date',
        'release_date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_no', 'patient_no');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class, 'bed_id');
    }
}
