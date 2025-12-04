<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Reset Password') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <div style="margin: 0 auto; display: flex; align-items: center; justify-center;">
            <img src="{{ url(asset('logo.png')) }}" alt="Motrac" style="height: 60px; width: auto; max-width: 200px;">
        </div>
    </div>
    
    <div style="background: #f9fafb; padding: 40px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb; border-top: none;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 24px;">{{ __('Reset Your Password') }}</h2>
        
        <p style="color: #4b5563; font-size: 16px;">
            {{ __('Hello') }} {{ $user->name }},
        </p>
        
        <p style="color: #4b5563; font-size: 16px;">
            {{ __('You are receiving this email because we received a password reset request for your account.') }}
        </p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetUrl }}" style="display: inline-block; background: #10B981; color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                {{ __('Reset Password') }}
            </a>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
            {{ __('This password reset link will expire in 60 minutes.') }}
        </p>
        
        <p style="color: #6b7280; font-size: 14px;">
            {{ __('If you did not request a password reset, no further action is required.') }}
        </p>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="color: #9ca3af; font-size: 12px; margin: 0;">
            {{ __('If you\'re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:') }}
        </p>
        <p style="color: #10B981; font-size: 12px; word-break: break-all; margin: 10px 0 0 0;">
            {{ $resetUrl }}
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: #9ca3af; font-size: 12px;">
        <p>&copy; {{ date('Y') }} Motrac. {{ __('All rights reserved.') }}</p>
    </div>
</body>
</html>
