@extends('layouts.auth')
@section('title', 'Log In — DAM Studio')

@section('content')
<h1 class="auth-title">Welcome back</h1>
<p class="auth-sub">Log in to your DAM Studio account to continue.</p>

<form method="POST" action="{{ route('login') }}" class="auth-form">
    @csrf

    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="form-input">
        @error('email')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password" class="form-input">
        @error('password')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-row auth-options">
        <label for="remember_me" class="auth-remember">
            <input id="remember_me" type="checkbox" name="remember">
            <span>Remember me</span>
        </label>
        @if (Route::has('password.request'))
            <a class="auth-link" href="{{ route('password.request') }}">Forgot your password?</a>
        @endif
    </div>

    <button type="submit" class="btn-primary auth-btn">Log In</button>

    <div class="auth-foot">
        Don't have an account? <a href="{{ route('register') }}" class="auth-link">Sign up</a>
    </div>
</form>
@endsection
