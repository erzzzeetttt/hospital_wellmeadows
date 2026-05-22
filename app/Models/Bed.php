<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bed extends Model
{
    protected $primaryKey = 'bed_id';

    protected $fillable = [
        'ward_id',
        'bed_number',
        'status',
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function activeAllocation(): HasOne
    {
        return $this->hasOne(WardAllocation::class, 'bed_id')->whereNull('release_date');
    }
}
