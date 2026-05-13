<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WardRequisitionItem extends Model
{
    protected $table = 'ward_requisition_items';

    protected $primaryKey = 'requisition_item_id';

    protected $fillable = [
        'requisition_id',
        'drug_id',
        'quantity_requested',
        'quantity_supplied',
    ];

    public function requisition()
    {
        return $this->belongsTo(WardRequisition::class, 'requisition_id');
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class, 'drug_id');
    }
}