<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $table = 'bills';

    protected $primaryKey = 'bill_id';

    protected $fillable = [
        'patient_no',
        'bill_date',
        'total_amount',
        'status',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_no', 'patient_no');
    }

    public function billItems()
    {
        return $this->hasMany(BillItem::class, 'bill_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'bill_id');
    }
}