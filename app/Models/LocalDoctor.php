<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalDoctor extends Model
{
    protected $table = 'local_doctors';

    protected $primaryKey = 'doctor_id';

    protected $fillable = [
        'fullname',
        'address',
        'telno',
    ];

    public function patients()
    {
        return $this->hasMany(Patient::class, 'doctor_id');
    }
}