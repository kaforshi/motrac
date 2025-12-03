<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'reconciled_balance',
        'actual_balance',
        'difference',
        'reconciled_at',
        'notes',
    ];

    protected $casts = [
        'reconciled_balance' => 'decimal:2',
        'actual_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'reconciled_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}


