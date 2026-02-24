<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasUuids;
    //
    protected $fillable = [
        'name',
        'code',
        'display_name',
        'is_active',
        'sort_order',
        'credentials',
        'environment',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credentials' => 'array',
    ];

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
