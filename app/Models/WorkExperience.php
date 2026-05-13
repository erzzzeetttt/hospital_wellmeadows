<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    protected $table = 'work_experiences';

    protected $primaryKey = 'experience_id';

    protected $fillable = [
        'staff_no',
        'organization',
        'position',
        'start_date',
        'end_date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_no');
    }
}