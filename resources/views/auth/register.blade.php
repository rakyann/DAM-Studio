@extends('layouts.auth')
@section('title', 'Sign Up — DAM Studio')

@section('content')
<h1 class="auth-title">Create an account</h1>
<p class="auth-sub">Join DAM Studio to start managing your 3D assets.</p>

<form method="POST" action="{{ route('register') }}" class="auth-form">
    @csrf

    <div class="form-group">
        <label for="name" class="form-label">Full Name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="form-input">
        @error('name')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="form-input">
        @error('email')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password" class="form-input">
        @error('password')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-input">
        @error('password_confirmation')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit" class="btn-primary auth-btn">Sign Up</button>

    <div class="auth-foot">
        Already registered? <a href="{{ route('login') }}" class="auth-link">Log in</a>
    </div>
</form>
@endsection
