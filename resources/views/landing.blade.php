<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'DAM Studio') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Three.js for 3D Viewer -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
</head>
<body class="antialiased">

    <!-- Global Nav -->
    <nav class="global-nav">
        <a href="{{ route('landing') }}">DAM Studio</a>
        <div class="nav-links">
            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <a href="#" onclick="event.preventDefault(); this.closest('form').submit();">Log out</a>
                </form>
            @else
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-split-section">
        <div class="hero-left">
            <span class="hero-badge">For Creators</span>
            
            @if($featuredAsset)
                <h1 class="hero-display">Showcase your 3D work <br><span class="hero-accent">and get discovered</span></h1>
            @else
                <h1 class="hero-display">DAM Studio <br><span class="hero-accent">3D Management</span></h1>
            @endif
            
            <ul class="hero-checklist">
                <li>
                    <svg viewBox="0 0 24 24" class="check-icon"><circle cx="12" cy="12" r="10" fill="var(--primary-on-dark)"/><path d="M7 12l3.5 3.5 7-7" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Upload .blend, .fbx, and .obj files seamlessly</span>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" class="check-icon"><circle cx="12" cy="12" r="10" fill="var(--primary-on-dark)"/><path d="M7 12l3.5 3.5 7-7" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Automatic conversion to web-ready .glb format</span>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" class="check-icon"><circle cx="12" cy="12" r="10" fill="var(--primary-on-dark)"/><path d="M7 12l3.5 3.5 7-7" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Keep files private or share with the community</span>
                </li>
            </ul>

            <div class="hero-actions">
                <a href="{{ route('register') }}" class="btn-primary">Get Started</a>
                <button class="btn-secondary" onclick="document.getElementById('grid-wrapper').scrollIntoView({behavior: 'smooth'})">Browse Gallery</button>
            </div>
        </div>

        <div class="hero-right">
            <div class="hero-viewer-frame">
                <div id="hero-canvas" class="hero-canvas"></div>
                
                @if(!$featuredAsset || !$featuredAsset->viewer_glb_path)
                <div class="hero-placeholder">
                    <p>No featured 3D model available yet.</p>
                </div>
                @else
                <div class="hero-viewer-meta">
                    <span class="meta-title">{{ $featuredAsset->title }}</span>
                    <span class="meta-author">By {{ $featuredAsset->user->name ?? 'Studio' }}</span>
                </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Discovery Grid Section -->
    <section class="discovery-section">
        <h2 class="display-lg section-title">Discover</h2>

        @if($popularTags->count() > 0)
        <div class="tags-bar">
            <button class="tag-filter-pill active" data-tag="">All</button>
            @foreach($popularTags as $tag)
                <button class="tag-filter-pill" data-tag="{{ $tag->slug }}">{{ $tag->name }}</button>
            @endforeach
        </div>
        @endif
        
        <div id="grid-wrapper">
            @include('partials.discovery_grid', ['assets' => $assets])
        </div>
    </section>

    <!-- Guest Gateway Modal -->
    <div id="guest-modal" class="modal-overlay">
        <div class="modal-content" style="position: relative;">
            <button class="modal-close" id="close-modal">&times;</button>
            <h3 class="display-lg" style="font-size: 24px;">Join DAM Studio</h3>
            <p>Sign up to download master files, leave comments, and interact with the community.</p>
            <div class="modal-actions">
                <a href="{{ route('register') }}" class="btn-primary" style="text-align: center;">Create Account</a>
                <a href="{{ route('login') }}" class="btn-secondary" style="text-align: center;">Log In</a>
            </div>
        </div>
    </div>

    <!-- Script for Hero Section 3D Viewer and Turntables -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('hero-canvas');
            const glbPath = "{{ $featuredAsset && $featuredAsset->viewer_glb_path ? asset('storage/' . $featuredAsset->viewer_glb_path) : '' }}";
            
            if (!glbPath) return;

            // Scene setup
            const scene = new THREE.Scene();
            
            // Camera
            const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 100);
            camera.position.set(0, 1.5, 4);

            // Renderer
            const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            
            // Adjust to container size instead of window
            const width = container.clientWidth;
            const height = container.clientHeight;
            renderer.setSize(width, height);
            renderer.setPixelRatio(window.devicePixelRatio);
            // Apple Design pedestal shadow equivalent in 3D: we rely on CSS or soft shadows
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            container.appendChild(renderer.domElement);

            // Controls
            const controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.enablePan = false;
            // No zoom limits explicitly set, allows scroll to zoom

            // Lighting
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
            scene.add(ambientLight);

            const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
            dirLight.position.set(5, 10, 7);
            dirLight.castShadow = true;
            scene.add(dirLight);

            // Load Model
            const loader = new THREE.GLTFLoader();
            loader.load(glbPath, function (gltf) {
                const model = gltf.scene;
                
                // Center model
                const box = new THREE.Box3().setFromObject(model);
                const center = box.getCenter(new THREE.Vector3());
                model.position.x += (model.position.x - center.x);
                model.position.y += (model.position.y - center.y);
                model.position.z += (model.position.z - center.z);
                
                // Add soft shadow plane
                const mesh = new THREE.Mesh(
                    new THREE.PlaneGeometry(10, 10),
                    new THREE.ShadowMaterial({ opacity: 0.22 })
                );
                mesh.rotation.x = -Math.PI / 2;
                mesh.position.y = box.min.y;
                mesh.receiveShadow = true;
                scene.add(mesh);

                model.traverse((child) => {
                    if (child.isMesh) {
                        child.castShadow = true;
                        child.receiveShadow = true;
                    }
                });

                scene.add(model);
            }, undefined, function (error) {
                console.error(error);
            });

            // Handle Resize
            window.addEventListener('resize', onWindowResize, false);
            function onWindowResize() {
                const width = container.clientWidth;
                const height = container.clientHeight;
                camera.aspect = width / height;
                camera.updateProjectionMatrix();
                renderer.setSize(width, height);
            }

            // Animation Loop
            function animate() {
                requestAnimationFrame(animate);
                controls.update();
                renderer.render(scene, camera);
            }
            animate();
        });

        // Tag Filtering Logic
        document.addEventListener('DOMContentLoaded', () => {
            const filterButtons = document.querySelectorAll('.tag-filter-pill');
            const gridWrapper = document.getElementById('grid-wrapper');

            filterButtons.forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    // Update active state
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
                        
                        // Re-initialize turntables for new DOM elements
                        initTurntables();
                    } catch (error) {
                        console.error('Error filtering assets:', error);
                    }
                });
            });
        });

        function initTurntables() {
            const cards = document.querySelectorAll('.asset-card');
            
            cards.forEach(card => {
                // ... same logic as before, refactored into function
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
                        model.rotation.y = 0; // reset
                    }
                });
            });

            // Bind Guest Gateway Modal
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
                // Check if user is auth by looking at a global var or just always showing modal for now
                // Actually, if they are unauthenticated, we show the modal.
                // We can inject a JS variable from Blade:
                const isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
                
                btn.addEventListener('click', (e) => {
                    if (!isAuthenticated) {
                        e.preventDefault();
                        e.stopPropagation();
                        guestModal.classList.add('active');
                    } else {
                        // Let it do whatever it was going to do, or maybe redirect
                        console.log('Action allowed');
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', initTurntables);
    </script>

</body>
</html>
