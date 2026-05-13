<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $primaryKey = 'bed_id';

    protected $fillable = [
        'ward_id',
        'bed_number',
        'status',
    ];

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }
}