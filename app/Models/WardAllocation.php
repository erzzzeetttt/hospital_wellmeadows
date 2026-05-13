<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_no');
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class, 'bed_id');
    }
}