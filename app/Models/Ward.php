<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ward extends Model
{
    protected $primaryKey = 'ward_id';

    protected $fillable = [
        'ward_name',
        'ward_type',
        'total_beds',
        'location',
        'charge_nurse',
        'telephone_extension',
    ];

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class, 'ward_id');
    }
}
