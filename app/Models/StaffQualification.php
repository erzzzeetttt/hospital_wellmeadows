<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffQualification extends Model
{
    protected $table = 'staff_qualifications';

    protected $primaryKey = 'qualification_id';

    protected $fillable = [
        'staff_no',
        'qualification_type',
        'institution',
        'date_obtained',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_no');
    }
}