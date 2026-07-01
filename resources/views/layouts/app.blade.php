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
    <a href="{{ route('assets.index') }}" class="nav-logo">
        <div class="nav-logo-box">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        </div>
        <span class="nav-logo-name">DAM Studio</span>
    </a>
    <div class="nav-links">
        <a href="{{ route('assets.index') }}" class="nav-link {{ request()->routeIs('assets.index') ? 'active' : '' }}">Library</a>
        <a href="{{ route('assets.create') }}" class="nav-link {{ request()->routeIs('assets.create') ? 'active' : '' }}">Upload</a>
    </div>
    <div class="nav-right">
        <a href="{{ route('assets.create') }}" class="nav-upload">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            Upload
        </a>
        <div class="nav-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-logout">Logout</button>
        </form>
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

@stack('scripts')
</body>
</html>