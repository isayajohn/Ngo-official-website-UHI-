@extends('emails.ughibrand')

@section('content')
    <h1 style="margin:0 0 16px;font-size:24px;line-height:1.25;">You Have Been Invited to UGHI</h1>
    <p>Hello {{ $invitation->first_name }},</p>
    <p>You have been invited to access the Ustawi Global Health Initiative management system.</p>
    <p><strong>Role:</strong> {{ $invitation->role?->name ?? 'Not assigned' }}<br>
        <strong>Department:</strong> {{ $invitation->primaryDepartment?->name ?? 'Not assigned' }}<br>
        <strong>Invited by:</strong> {{ $invitation->inviter?->name ?? 'UGHI administrator' }}</p>
    <p style="margin:28px 0;">
        <a href="{{ $url }}" style="display:inline-block;border-radius:8px;background:#0870bd;padding:12px 20px;color:#fff;font-weight:bold;text-decoration:none;">Accept Invitation</a>
    </p>
    <p>This invitation expires on {{ $invitation->expires_at->format('Y-m-d H:i T') }}.</p>
    <p>If you were not expecting this invitation, you may ignore this email.</p>
@endsection
