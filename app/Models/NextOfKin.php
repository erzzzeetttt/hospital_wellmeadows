<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NextOfKin extends Model
{
    protected $table = 'next_of_kin';

    protected $primaryKey = 'nextofkinid';

    protected $fillable = [
        'fullname',
        'relationshiptopatient',
        'address',
        'telno',
    ];

    public function patients()
    {
        return $this->hasMany(Patient::class, 'nextofkinid');
    }
}