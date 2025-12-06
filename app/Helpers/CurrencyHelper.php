<?php

namespace App\Helpers;

class CurrencyHelper
{
    public static function format($amount, $currency = null)
    {
        $user = auth()->user();
        $currency = $currency ?? ($user->currency ?? 'IDR');
        
        $symbols = [
            'IDR' => 'Rp',
            'USD' => '$',
        ];
        
        $symbol = $symbols[$currency] ?? $currency;
        
        // Format number based on currency
        if ($currency === 'USD') {
            return $symbol . ' ' . number_format($amount, 2, '.', ',');
        } else {
            return $symbol . ' ' . number_format($amount, 0, ',', '.');
        }
    }
    
    public static function getSymbol($currency = null)
    {
        $user = auth()->user();
        $currency = $currency ?? ($user->currency ?? 'IDR');
        
        $symbols = [
            'IDR' => 'Rp',
            'USD' => '$',
        ];
        
        return $symbols[$currency] ?? $currency;
    }
    
    public static function getCurrency()
    {
        $user = auth()->user();
        return $user->currency ?? 'IDR';
    }
}







