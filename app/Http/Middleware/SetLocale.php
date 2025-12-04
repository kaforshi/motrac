<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip locale setting for language switch route to avoid conflicts
        if ($request->is('language/*')) {
            return $next($request);
        }
        
        // Check if locale is set in session, otherwise default to 'id'
        $locale = Session::get('locale');
        
        if (!$locale || !in_array($locale, ['id', 'en'])) {
            $locale = 'id'; // Default to Indonesian
            Session::put('locale', $locale);
        }
        
        // Set locale for the application
        App::setLocale($locale);
        
        // Also set it in config to ensure it persists
        config(['app.locale' => $locale]);
        
        // Make locale available to all views
        view()->share('currentLocale', $locale);
        
        return $next($request);
    }
}

