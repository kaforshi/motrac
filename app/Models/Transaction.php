<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';
    const TYPE_TRANSFER = 'transfer';

    protected $fillable = [
        'user_id',
        'account_id',
        'from_account_id',
        'to_account_id',
        'category_id',
        'type',
        'amount',
        'description',
        'date',
        'notes',
        'is_split',
        'parent_transaction_id',
        'receipt_path',
        'labels',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'is_split' => 'boolean',
        'labels' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function parentTransaction()
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    public function splitTransactions()
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id');
    }

    public function isTransfer()
    {
        return $this->type === self::TYPE_TRANSFER;
    }
}








