<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';

    protected $primaryKey = 'staff_no';

    protected $fillable = [
        'role_id',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'address',
        'phone_no',
        'position',
        'salary',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}