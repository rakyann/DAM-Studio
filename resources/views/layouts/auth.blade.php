<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DAM Studio')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            margin: 0;
            padding: 0;
            background: var(--canvas-parchment);
            font-family: var(--font-family);
            color: var(--body);
            overflow-x: hidden;
        }

        .auth-split-layout {
            display: flex;
            min-height: 100vh;
        }

        .auth-visual-side {
            display: none;
            flex: 1;
            background: radial-gradient(circle at top left, #1e1b4b, #0f172a);
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 1024px) {
            .auth-visual-side {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        .auth-form-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 24px;
            position: relative;
            z-index: 10;
        }

        .auth-visual-content {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 60px;
        }

        .auth-visual-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" stroke="rgba(255,255,255,0.05)" stroke-width="2" fill="none" /><circle cx="50" cy="50" r="30" stroke="rgba(255,255,255,0.05)" stroke-width="2" fill="none" /></svg>');
            background-size: 800px;
            background-position: center;
            opacity: 0.5;
            animation: spinBg 100s linear infinite;
        }

        @keyframes spinBg {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .glass-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--rounded-lg);
            padding: 48px 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .auth-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--on-dark);
            text-decoration: none;
            margin-bottom: 40px;
            font-size: 24px;
            font-weight: 700;
        }

        .auth-logo-box {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: var(--rounded-sm);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-logo-box svg {
            width: 20px;
            height: 20px;
            color: white;
        }

        .auth-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--on-dark);
            margin-bottom: 8px;
        }

        .auth-sub {
            color: var(--body-muted);
            margin-bottom: 32px;
            font-size: 15px;
        }

        .form-label {
            color: var(--ink-muted-80);
            font-size: 14px;
            margin-bottom: 6px;
            display: block;
        }

        .form-input {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--rounded-sm);
            padding: 12px 16px;
            color: var(--on-dark);
            transition: all 0.2s;
        }

        .form-input:focus {
            border-color: var(--primary-focus);
            outline: none;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }
        
        .auth-btn-submit {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            border-radius: var(--rounded-sm);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 24px;
        }

        .auth-btn-submit:hover {
            background: var(--primary-focus);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="auth-split-layout">
    <div class="auth-visual-side">
        <div class="auth-visual-bg"></div>
        <div class="auth-visual-content">
            <h2 class="hero-display text-gradient" style="font-size: 48px; margin-bottom: 16px;">Unleash your creativity.</h2>
            <p style="font-size: 18px; color: var(--body-muted); max-width: 400px; margin: 0 auto;">Join the platform where professional creators manage, convert, and showcase their best 3D work.</p>
        </div>
    </div>
    <div class="auth-form-side">
        <div class="glow-bg" style="width: 200%; height: 200%; opacity: 0.5;"></div>
        
        <a href="{{ route('landing') }}" class="auth-logo">
            <div class="auth-logo-box">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <span>DAM Studio</span>
        </a>

        <div class="glass-card animate-fade-in-up">
            @yield('content')
        </div>
    </div>
</div>

</body>
</html>
