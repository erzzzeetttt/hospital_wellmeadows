<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentStaff extends Model
{
    protected $table = 'treatment_staff';

    protected $fillable = [
        'treatment_id',
        'staff_no',
    ];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class, 'treatment_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_no');
    }
}