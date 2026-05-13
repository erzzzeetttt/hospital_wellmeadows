<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WardRequisition extends Model
{
    protected $table = 'ward_requisitions';

    protected $primaryKey = 'requisition_id';

    protected $fillable = [
        'ward_id',
        'staff_no',
        'requisition_date',
        'status',
    ];

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_no');
    }

    public function requisitionItems()
    {
        return $this->hasMany(WardRequisitionItem::class, 'requisition_id');
    }
}