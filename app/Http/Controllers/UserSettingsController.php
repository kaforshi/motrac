<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function updateSettings(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'privacy_mode' => 'boolean',
            'dark_mode' => 'boolean',
        ]);

        $user->update($validated);

        return response()->json(['success' => true, 'message' => 'Settings updated successfully.']);
    }
}







