<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasUuids;
    //
    protected $fillable = [
        'id',
        'name',
        'display_name',
        'is_active',
        'priority',
        'credentials',
        'environment',
    ];

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
