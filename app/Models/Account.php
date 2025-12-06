<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    const TYPE_CASH = 'cash';
    const TYPE_BANK = 'bank';
    const TYPE_EWALLET = 'ewallet';
    const TYPE_LIABILITY = 'liability';
    const TYPE_INVESTMENT = 'investment';

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'balance',
        'initial_balance',
        'currency',
        'is_hidden',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'initial_balance' => 'decimal:2',
        'is_hidden' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    public function transfersFrom()
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    public function transfersTo()
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }

    public function reconciliations()
    {
        return $this->hasMany(AccountReconciliation::class);
    }

    public function getVisibleBalanceAttribute()
    {
        if ($this->is_hidden) {
            return 0;
        }
        return $this->balance;
    }
}





