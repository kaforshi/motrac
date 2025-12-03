<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'month',
        'year',
        'rollover_enabled',
        'rollover_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'rollover_amount' => 'decimal:2',
        'rollover_enabled' => 'boolean',
        'month' => 'integer',
        'year' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getSpentAttribute()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        return Transaction::where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->where('type', Transaction::TYPE_EXPENSE)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');
    }

    public function getRemainingAttribute()
    {
        $available = $this->amount + ($this->rollover_enabled ? $this->rollover_amount : 0);
        return $available - $this->spent;
    }

    public function getPercentageAttribute()
    {
        $available = $this->amount + ($this->rollover_enabled ? $this->rollover_amount : 0);
        if ($available == 0) return 0;
        return min(100, ($this->spent / $available) * 100);
    }

    public function getStatusAttribute()
    {
        $percentage = $this->percentage;
        if ($percentage >= 100) return 'danger';
        if ($percentage >= 80) return 'warning';
        return 'safe';
    }
}

