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
        // Always use dot (.) for thousands separator and comma (,) for decimal
        if ($currency === 'USD') {
            return $symbol . ' ' . number_format($amount, 2, ',', '.');
        } else {
            return $symbol . ' ' . number_format($amount, 0, ',', '.');
        }
    }
}

if (!function_exists('formatNumber')) {
    /**
     * Format number with dot as thousands separator (Indonesian format)
     * 
     * @param float|int $number
     * @param int $decimals Number of decimal places (0 for integers)
     * @return string
     */
    function formatNumber($number, $decimals = 0)
    {
        return number_format($number, $decimals, ',', '.');
    }
}
