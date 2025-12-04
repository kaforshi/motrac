<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}

