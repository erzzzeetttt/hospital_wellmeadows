<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'role_id';

    protected $fillable = [
        'role_name',
        'description',
    ];

    public function staff()
    {
        return $this->hasMany(Staff::class, 'role_id');
    }
}