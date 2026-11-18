@extends('emails.ughibrand')

@section('content')
    <h1 style="margin:0 0 16px;font-size:24px;line-height:1.25;">Reset Your UGHI Password</h1>
    <p>Hello {{ $firstName }},</p>
    <p>We received a request to reset your password for your UGHI account.</p>
    <p style="margin:28px 0;">
        <a href="{{ $url }}" style="display:inline-block;border-radius:8px;background:#0870bd;padding:12px 20px;color:#fff;font-weight:bold;text-decoration:none;">Reset Password</a>
    </p>
    <p>This link expires in {{ $expiryMinutes }} minutes. Ignore this email if you did not request the reset, and never share the link.</p>
@endsection
