<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    protected $table = 'bill_items';

    protected $primaryKey = 'bill_item_id';

    protected $fillable = [
        'bill_id',
        'item_description',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }
}