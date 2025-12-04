<?php

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currency = null)
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
}
