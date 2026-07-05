@extends('layouts.auth')
@section('title', 'Sign Up — DAM Studio')

@section('content')
<h1 class="auth-title">Create an account</h1>
<p class="auth-sub">Join DAM Studio to start managing your 3D assets.</p>

<form method="POST" action="{{ route('register') }}" class="auth-form">
    @csrf

    <div style="margin-bottom: 20px;">
        <label for="name" class="form-label">Full Name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="form-input">
        @error('name')
            <span style="color: #ef4444; font-size: 13px; margin-top: 4px; display: block;">{{ $message }}</span>
        @enderror
    </div>

    <div style="margin-bottom: 20px;">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="form-input">
        @error('email')
            <span style="color: #ef4444; font-size: 13px; margin-top: 4px; display: block;">{{ $message }}</span>
        @enderror
    </div>

    <div style="margin-bottom: 20px;">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password" class="form-input">
        @error('password')
            <span style="color: #ef4444; font-size: 13px; margin-top: 4px; display: block;">{{ $message }}</span>
        @enderror
    </div>

    <div style="margin-bottom: 32px;">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-input">
        @error('password_confirmation')
            <span style="color: #ef4444; font-size: 13px; margin-top: 4px; display: block;">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit" class="auth-btn-submit">Sign Up</button>

    <div style="text-align: center; margin-top: 24px; font-size: 14px; color: var(--ink-muted-80);">
        Already registered? <a href="{{ route('login') }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">Log in</a>
    </div>
</form>
@endsection
