<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $primaryKey = 'supplierno';

    protected $fillable = [
        'suppliername',
        'address',
        'telno',
    ];

    public function drugs()
    {
        return $this->hasMany(Drug::class, 'supplierno');
    }
}