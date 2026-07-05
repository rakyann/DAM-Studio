@extends('layouts.auth')
@section('title', 'Log In — DAM Studio')

@section('content')
<h1 class="auth-title">Welcome back</h1>
<p class="auth-sub">Enter your details to access your studio.</p>

<form method="POST" action="{{ route('login') }}" class="auth-form">
    @csrf

    <div style="margin-bottom: 20px;">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="form-input">
        @error('email')
            <span style="color: #ef4444; font-size: 13px; margin-top: 4px; display: block;">{{ $message }}</span>
        @enderror
    </div>

    <div style="margin-bottom: 24px;">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password" class="form-input">
        @error('password')
            <span style="color: #ef4444; font-size: 13px; margin-top: 4px; display: block;">{{ $message }}</span>
        @enderror
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; font-size: 14px;">
        <label for="remember_me" style="display: flex; align-items: center; gap: 8px; color: var(--ink-muted-80); cursor: pointer;">
            <input id="remember_me" type="checkbox" name="remember" style="accent-color: var(--primary);">
            <span>Remember me</span>
        </label>
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">Forgot password?</a>
        @endif
    </div>

    <button type="submit" class="auth-btn-submit">Log In</button>

    <div style="text-align: center; margin-top: 24px; font-size: 14px; color: var(--ink-muted-80);">
        Don't have an account? <a href="{{ route('register') }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">Sign up</a>
    </div>
</form>
@endsection
