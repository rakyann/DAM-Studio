<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DAM Studio')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <a href="{{ route('assets.index') }}" class="nav-logo">
            <div class="nav-logo-box">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <span class="nav-logo-name">DAM Studio</span>
        </a>
    </div>
    <div class="nav-links">
        <a href="{{ route('assets.index') }}" class="nav-link {{ request()->routeIs('assets.index') ? 'active' : '' }}">Library</a>
        <a href="{{ route('assets.create') }}" class="nav-link {{ request()->routeIs('assets.create') ? 'active' : '' }}">Upload</a>
    </div>
    <div class="nav-right">
        <div class="nav-action-pill">
            <a href="{{ route('assets.create') }}" class="nav-upload-btn">+ Upload</a>
            <a href="{{ route('profile.edit') }}" class="nav-avatar" title="Profile">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="nav-logout-icon" title="Logout">
                    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                </button>
            </form>
        </div>
    </div>
</nav>

@if(session('success'))
    <div class="flash flash-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">{{ session('error') }}</div>
@endif

<div class="page">
    @yield('content')
</div>

<footer class="site-footer">
    <div class="footer-top">
        <div class="footer-brand">
            <a href="{{ route('assets.index') }}" class="nav-logo">
                <div class="nav-logo-box">
                    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                </div>
                <span class="nav-logo-name">DAM Studio</span>
            </a>
            <p class="footer-desc">Your studio's 3D asset library — all in one place.<br>Upload, convert, and preview models seamlessly.</p>
            <div class="footer-subscribe">
                <input type="email" placeholder="Email address" class="subscribe-input">
                <button class="btn-primary subscribe-btn">Subscribe</button>
            </div>
        </div>
        <div class="footer-links-grid">
            <div class="footer-col">
                <h4>Platform</h4>
                <a href="#">Library</a>
                <a href="#">Upload</a>
                <a href="#">Tags</a>
            </div>
            <div class="footer-col">
                <h4>Resources</h4>
                <a href="#">Documentation</a>
                <a href="#">API Reference</a>
                <a href="#">Help Center</a>
            </div>
            <div class="footer-col">
                <h4>Company</h4>
                <a href="#">About Us</a>
                <a href="#">Careers</a>
                <a href="#">Contact</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="footer-copy">&copy; {{ date('Y') }} DAM Studio. All rights reserved.</div>
        <div class="footer-legal">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>