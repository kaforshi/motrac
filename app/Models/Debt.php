<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    use HasFactory;

    const TYPE_PAYABLE = 'payable'; // Utang
    const TYPE_RECEIVABLE = 'receivable'; // Piutang

    protected $fillable = [
        'user_id',
        'account_id',
        'type',
        'contact_name',
        'contact_phone',
        'contact_email',
        'initial_amount',
        'current_amount',
        'due_date',
        'description',
        'is_paid',
        'paid_at',
    ];

    protected $casts = [
        'initial_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'due_date' => 'date',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function payments()
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function getRemainingAmountAttribute()
    {
        $paid = $this->payments()->sum('amount');
        return $this->initial_amount - $paid;
    }

    public function isOverdue()
    {
        return !$this->is_paid && $this->due_date && $this->due_date->isPast();
    }
}



