<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $primaryKey = 'ward_id';

    protected $fillable = [
        'ward_name',
        'total_beds',
        'location',
    ];

    public function beds()
    {
        return $this->hasMany(Bed::class, 'ward_id');
    }
}