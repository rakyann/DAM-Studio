<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'DAM Studio') }} - Premium 3D Asset Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Three.js for 3D Viewer -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    
    <style>
        .hero-section-new {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 24px 80px 24px;
            overflow: hidden;
            background: radial-gradient(circle at center, rgba(30, 41, 59, 0.8) 0%, var(--canvas) 100%);
        }
        
        .hero-content {
            position: relative;
            z-index: 10;
            max-width: 1200px;
            width: 100%;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 32px;
        }

        .hero-bg-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: auto;
            opacity: 0.6;
        }

        .features-section {
            padding: 100px 40px;
            background: var(--canvas);
            position: relative;
            z-index: 10;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            padding: 40px;
            border-radius: var(--rounded-lg);
            text-align: left;
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--rounded-md);
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary-on-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }
        
        .feature-icon svg {
            width: 28px;
            height: 28px;
        }

        .feature-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--on-dark);
            margin-bottom: 16px;
        }

        .feature-desc {
            font-size: 16px;
            color: var(--body-muted);
            line-height: 1.6;
        }

        /* Nav overrides for landing */
        .global-nav.glass-panel {
            border-bottom: 1px solid var(--hairline);
            height: 64px;
        }
        .global-nav a {
            font-size: 14px;
            font-weight: 500;
            padding: 8px 16px;
        }
        .nav-btn-primary {
            background: var(--primary);
            color: var(--on-primary) !important;
            border-radius: var(--rounded-pill);
            transition: all 0.2s;
        }
        .nav-btn-primary:hover {
            background: var(--primary-focus);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }
    </style>
</head>
<body class="antialiased">
    <div class="glow-bg"></div>

    <!-- Global Nav -->
    <nav class="global-nav glass-panel" style="position: fixed; width: 100%; top: 0; z-index: 1000; display: flex; align-items: center; justify-content: space-between; padding: 0 40px;">
        <a href="{{ route('landing') }}" style="font-size: 20px; font-weight: 700; color: var(--on-dark); display: flex; align-items: center; gap: 8px;">
            <div style="width: 32px; height: 32px; background: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 24 24" style="width: 18px; height: 18px; color: white;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            DAM Studio
        </a>
        <div class="nav-links" style="display: flex; align-items: center; gap: 16px;">
            @auth
                <a href="{{ route('dashboard') }}" class="nav-btn-primary">Go to Dashboard</a>
            @else
                <a href="{{ route('login') }}" style="color: var(--body-muted);">Log in</a>
                <a href="{{ route('register') }}" class="nav-btn-primary">Get Started</a>
            @endauth
        </div>
    </nav>

    <!-- Immersive Hero Section -->
    <section class="hero-section-new">
        <!-- The 3D Canvas will be absolutely positioned behind the content -->
        <div id="hero-bg-canvas" class="hero-bg-canvas"></div>
        
        <div class="hero-content animate-fade-in-up">
            <span class="hero-badge glass-panel" style="display: inline-block; margin: 0 auto; padding: 8px 24px; font-size: 14px; letter-spacing: 1px; text-transform: uppercase; text-align: center;">Next-Gen Asset Management</span>
            
            <h1 class="hero-display" style="font-size: 72px; line-height: 1.1;">
                Elevate your 3D workflow <br>
                <span class="text-gradient">in the cloud.</span>
            </h1>
            
            <p class="lead" style="color: var(--body-muted); max-width: 600px;">
                Seamlessly upload, convert, and showcase your 3D models. A premium experience built for professional creators and studios.
            </p>

            <div class="hero-actions" style="margin-top: 24px; display: flex; gap: 20px; justify-content: center;">
                <a href="{{ route('register') }}" class="btn-primary hover-scale" style="padding: 16px 32px; font-size: 18px; font-weight: 600;">Start for free</a>
                <button class="btn-secondary glass-panel hover-scale" style="padding: 16px 32px; font-size: 18px; color: var(--on-dark); border-color: rgba(255,255,255,0.2);" onclick="document.getElementById('grid-wrapper').scrollIntoView({behavior: 'smooth'})">Explore Gallery</button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div style="text-align: center; margin-bottom: 60px;" class="animate-fade-in-up">
            <h2 class="display-lg text-gradient">Why DAM Studio?</h2>
            <p style="color: var(--body-muted); font-size: 18px; margin-top: 16px;">Everything you need to manage 3D assets in one place.</p>
        </div>
        
        <div class="feature-grid">
            <div class="feature-card glass-panel hover-scale">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <h3 class="feature-title">Universal Uploads</h3>
                <p class="feature-desc">Drag and drop .blend, .fbx, or .obj files. We handle the heavy lifting of parsing and storing your master files securely.</p>
            </div>
            
            <div class="feature-card glass-panel hover-scale">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <h3 class="feature-title">Auto Conversion</h3>
                <p class="feature-desc">Our backend automatically converts your complex models into web-optimized .glb formats for lightning-fast rendering.</p>
            </div>

            <div class="feature-card glass-panel hover-scale">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
                <h3 class="feature-title">Immersive Viewer</h3>
                <p class="feature-desc">Showcase your assets with our built-in 3D viewer featuring interactive turntables, soft shadows, and responsive controls.</p>
            </div>
        </div>
    </section>

    <!-- Discovery Grid Section -->
    <section class="discovery-section" style="padding: 100px 40px; background: var(--canvas-parchment); position: relative; z-index: 10;">
        <div style="text-align: center; margin-bottom: 40px;">
            <h2 class="display-lg">Community Gallery</h2>
        </div>

        @if($popularTags->count() > 0)
        <div class="tags-bar">
            <button class="tag-filter-pill active glass-panel" data-tag="">All</button>
            @foreach($popularTags as $tag)
                <button class="tag-filter-pill glass-panel" data-tag="{{ $tag->slug }}">{{ $tag->name }}</button>
            @endforeach
        </div>
        @endif
        
        <div id="grid-wrapper">
            @include('partials.discovery_grid', ['assets' => $assets])
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: var(--surface-black); padding: 60px 40px; border-top: 1px solid rgba(255,255,255,0.05); position: relative; z-index: 10;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; md:flex-row; justify-content: space-between; align-items: center; gap: 24px;">
            <div style="display: flex; align-items: center; gap: 12px; color: var(--on-dark);">
                <div style="width: 32px; height: 32px; background: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" style="width: 18px; height: 18px; color: white;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                </div>
                <span style="font-size: 20px; font-weight: 700;">DAM Studio</span>
            </div>
            
            <div style="color: var(--body-muted); font-size: 14px; text-align: center;">
                &copy; {{ date('Y') }} DAM Studio. Built for 3D Creators.
            </div>
            
            <div style="display: flex; gap: 16px;">
                <a href="#" style="color: var(--body-muted); text-decoration: none; font-size: 14px; transition: color 0.2s;" onmouseover="this.style.color='var(--on-dark)'" onmouseout="this.style.color='var(--body-muted)'">Privacy</a>
                <a href="#" style="color: var(--body-muted); text-decoration: none; font-size: 14px; transition: color 0.2s;" onmouseover="this.style.color='var(--on-dark)'" onmouseout="this.style.color='var(--body-muted)'">Terms</a>
            </div>
        </div>
    </footer>
    <!-- Guest Gateway Modal -->
    <div id="guest-modal" class="modal-overlay">
        <div class="modal-content glass-panel" style="position: relative;">
            <button class="modal-close" id="close-modal">&times;</button>
            <h3 class="display-lg" style="font-size: 28px; margin-bottom: 16px;">Join DAM Studio</h3>
            <p style="font-size: 16px;">Sign up to download master files, leave comments, and interact with the community.</p>
            <div class="modal-actions" style="margin-top: 32px;">
                <a href="{{ route('register') }}" class="btn-primary hover-scale" style="text-align: center; padding: 14px;">Create Free Account</a>
                <a href="{{ route('login') }}" class="btn-secondary glass-panel hover-scale" style="text-align: center; padding: 14px; color: var(--on-dark);">Log In</a>
            </div>
        </div>
    </div>

    <!-- Script for Hero Section 3D Viewer and Turntables -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('hero-bg-canvas');
            const glbPath = "{{ $featuredAsset && $featuredAsset->viewer_glb_path ? asset('storage/' . $featuredAsset->viewer_glb_path) : '' }}";
            
            // If no featured asset, we can render a default geometric shape for aesthetics
            
            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 100);
            camera.position.set(0, 1.5, 6); // pulled back slightly for background effect

            const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(window.innerWidth, window.innerHeight); // full window size
            renderer.setPixelRatio(window.devicePixelRatio);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            container.appendChild(renderer.domElement);

            const controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.enablePan = false;
            controls.enableZoom = false; // Disable zoom for background so scroll works on page
            controls.autoRotate = true;
            controls.autoRotateSpeed = 1.0;

            const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
            scene.add(ambientLight);

            const dirLight = new THREE.DirectionalLight(0xa5b4fc, 1);
            dirLight.position.set(5, 10, 7);
            dirLight.castShadow = true;
            scene.add(dirLight);
            
            const fillLight = new THREE.DirectionalLight(0x818cf8, 0.5);
            fillLight.position.set(-5, 5, -5);
            scene.add(fillLight);

            if (glbPath) {
                const loader = new THREE.GLTFLoader();
                loader.load(glbPath, function (gltf) {
                    const model = gltf.scene;
                    const box = new THREE.Box3().setFromObject(model);
                    const center = box.getCenter(new THREE.Vector3());
                    model.position.x += (model.position.x - center.x);
                    model.position.y += (model.position.y - center.y);
                    model.position.z += (model.position.z - center.z);
                    
                    const mesh = new THREE.Mesh(
                        new THREE.PlaneGeometry(20, 20),
                        new THREE.ShadowMaterial({ opacity: 0.15 })
                    );
                    mesh.rotation.x = -Math.PI / 2;
                    mesh.position.y = box.min.y - 0.1;
                    mesh.receiveShadow = true;
                    scene.add(mesh);

                    model.traverse((child) => {
                        if (child.isMesh) {
                            child.castShadow = true;
                            child.receiveShadow = true;
                        }
                    });

                    scene.add(model);
                });
            } else {
                // Render an abstract shape if no featured asset
                const geometry = new THREE.IcosahedronGeometry(1.5, 1);
                const material = new THREE.MeshPhysicalMaterial({ 
                    color: 0x4f46e5,
                    metalness: 0.7,
                    roughness: 0.2,
                    wireframe: true
                });
                const shape = new THREE.Mesh(geometry, material);
                scene.add(shape);
            }

            window.addEventListener('resize', () => {
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(window.innerWidth, window.innerHeight);
            }, false);

            function animate() {
                requestAnimationFrame(animate);
                controls.update();
                renderer.render(scene, camera);
            }
            animate();
        });

        // Tag Filtering Logic & Turntables (unchanged logic, just re-integrated)
        document.addEventListener('DOMContentLoaded', () => {
            const filterButtons = document.querySelectorAll('.tag-filter-pill');
            const gridWrapper = document.getElementById('grid-wrapper');

            filterButtons.forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    filterButtons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    const tag = btn.getAttribute('data-tag');
                    try {
                        const response = await fetch(`/?tag=${tag}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();
                        gridWrapper.innerHTML = data.html;
                        initTurntables();
                    } catch (error) {}
                });
            });
        });

        function initTurntables() {
            const cards = document.querySelectorAll('.asset-card');
            
            cards.forEach(card => {
                const glbPath = card.getAttribute('data-glb');
                const canvasContainer = card.querySelector('.turntable-canvas');
                
                if (!glbPath || !canvasContainer) return;

                let scene, camera, renderer, model, reqId;
                let isInitialized = false;

                card.addEventListener('mouseenter', () => {
                    if (!isInitialized) {
                        scene = new THREE.Scene();
                        camera = new THREE.PerspectiveCamera(45, canvasContainer.clientWidth / canvasContainer.clientHeight, 0.1, 100);
                        camera.position.set(0, 1, 3);
                        
                        renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
                        renderer.setSize(canvasContainer.clientWidth, canvasContainer.clientHeight);
                        canvasContainer.appendChild(renderer.domElement);
                        
                        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
                        scene.add(ambientLight);
                        const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
                        dirLight.position.set(2, 5, 3);
                        scene.add(dirLight);

                        const loader = new THREE.GLTFLoader();
                        loader.load(glbPath, (gltf) => {
                            model = gltf.scene;
                            const box = new THREE.Box3().setFromObject(model);
                            const center = box.getCenter(new THREE.Vector3());
                            model.position.x += (model.position.x - center.x);
                            model.position.y += (model.position.y - center.y);
                            model.position.z += (model.position.z - center.z);
                            scene.add(model);
                        });
                        isInitialized = true;
                    }

                    function animateTurntable() {
                        reqId = requestAnimationFrame(animateTurntable);
                        if (model) {
                            model.rotation.y += 0.02;
                        }
                        if (renderer && scene && camera) {
                            renderer.render(scene, camera);
                        }
                    }
                    animateTurntable();
                });

                card.addEventListener('mouseleave', () => {
                    if (reqId) {
                        cancelAnimationFrame(reqId);
                        reqId = null;
                    }
                    if (model) {
                        model.rotation.y = 0;
                    }
                });
            });

            const guestModal = document.getElementById('guest-modal');
            const closeModal = document.getElementById('close-modal');
            
            if (closeModal) {
                closeModal.addEventListener('click', () => {
                    guestModal.classList.remove('active');
                });
            }

            guestModal.addEventListener('click', (e) => {
                if (e.target === guestModal) {
                    guestModal.classList.remove('active');
                }
            });

            const restrictedActions = document.querySelectorAll('.restricted-action');
            restrictedActions.forEach(btn => {
                const isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
                
                btn.addEventListener('click', (e) => {
                    if (!isAuthenticated) {
                        e.preventDefault();
                        e.stopPropagation();
                        guestModal.classList.add('active');
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', initTurntables);
        
        // Add scroll fade animations
        document.addEventListener("DOMContentLoaded", function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in-up');
                        observer.unobserve(entry.target);
                    }
                });
            });

            document.querySelectorAll('.feature-card').forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>
