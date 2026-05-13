<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    protected $table = 'drugs';

    protected $primaryKey = 'drug_id';

    protected $fillable = [
        'supplierno',
        'drug_name',
        'quantity_stock',
        'unit_cost',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplierno');
    }

    public function medicationRecords()
    {
        return $this->hasMany(MedicationRecord::class, 'drug_id');
    }
}