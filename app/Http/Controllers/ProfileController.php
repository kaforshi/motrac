<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;
use App\Models\Account;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get statistics
        $transactionCount = Transaction::where('user_id', $user->id)->count();
        $accountCount = Account::where('user_id', $user->id)->count();
        
        return view('profile', compact('user', 'transactionCount', 'accountCount'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'bio' => 'nullable|string|max:500',
        ]);

        $user->name = trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? ''));
        $user->email = $validated['email'];
        
        // Add bio if column exists
        if (isset($validated['bio'])) {
            $user->bio = $validated['bio'];
        }
        
        $user->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui!',
                'user' => $user
            ]);
        }

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function updatePhoto(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Delete old photo if exists
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        // Store new photo
        $photoPath = $request->file('photo')->store('profile-photos', 'public');
        $user->photo = $photoPath;
        $user->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui!',
                'photo_url' => Storage::url($photoPath)
            ]);
        }

        return back()->with('success', 'Foto profil berhasil diperbarui!');
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'currency' => 'nullable|string|max:3',
            'dark_mode' => 'boolean',
            'timezone' => 'nullable|string|max:50',
        ]);

        if (isset($validated['currency'])) {
            $user->currency = $validated['currency'];
        }
        
        if (isset($validated['dark_mode'])) {
            $user->dark_mode = $validated['dark_mode'];
        }
        
        if (isset($validated['timezone'])) {
            $user->timezone = $validated['timezone'];
        }
        
        $user->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Preferensi berhasil diperbarui!',
                'user' => $user
            ]);
        }

        return back()->with('success', 'Preferensi berhasil diperbarui!');
    }
    
    public function updateTimezone(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'timezone' => 'required|string|max:50',
        ]);

        $user->timezone = $validated['timezone'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Timezone berhasil diperbarui!',
            'timezone' => $user->timezone
        ]);
    }

    public function updateNotificationPreferences(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'notify_security' => 'boolean',
            'notify_budget' => 'boolean',
            'notify_weekly_report' => 'boolean',
        ]);

        $user->notify_security = $validated['notify_security'] ?? true;
        $user->notify_budget = $validated['notify_budget'] ?? true;
        $user->notify_weekly_report = $validated['notify_weekly_report'] ?? false;
        
        $user->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Preferensi notifikasi berhasil diperbarui!',
                'user' => $user
            ]);
        }

        return back()->with('success', 'Preferensi notifikasi berhasil diperbarui!');
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password saat ini tidak sesuai.',
                    'errors' => ['current_password' => ['Password saat ini tidak sesuai.']]
                ], 422);
            }
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah!'
            ]);
        }

        return back()->with('success', 'Password berhasil diubah!');
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'password' => 'required',
            'confirm_text' => 'required|in:HAPUS',
        ]);

        if (!Hash::check($validated['password'], $user->password)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password tidak sesuai.',
                    'errors' => ['password' => ['Password tidak sesuai.']]
                ], 422);
            }
            return back()->withErrors(['password' => 'Password tidak sesuai.']);
        }

        // Delete user photo if exists
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        // Logout user before deleting
        Auth::logout();

        // Delete user (cascade will handle related data)
        $user->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Akun berhasil dihapus.',
                'redirect' => route('landing')
            ]);
        }

        return redirect()->route('landing')->with('success', 'Akun berhasil dihapus.');
    }
}

