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
    <section class="hero-section">
        <div id="hero-canvas" class="hero-canvas"></div>
        
        <div class="hero-content">
            @if($featuredAsset)
                <h1 class="hero-display">{{ $featuredAsset->title }}</h1>
                <p class="lead">By {{ $featuredAsset->user->name ?? 'Studio' }}</p>
            @else
                <h1 class="hero-display">DAM Studio</h1>
                <p class="lead">The future of 3D asset management.</p>
            @endif
        </div>

        @if(!$featuredAsset || !$featuredAsset->viewer_glb_path)
        <div class="hero-placeholder" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            <p>No featured 3D model available yet.</p>
        </div>
        @endif
    </section>

    <!-- Discovery Grid Section -->
    <section class="discovery-section">
        <h2 class="display-lg section-title">Discover</h2>
        
        <div id="grid-wrapper">
            @include('partials.discovery_grid', ['assets' => $assets])
        </div>
    </section>

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
            renderer.setSize(window.innerWidth, window.innerHeight);
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
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(window.innerWidth, window.innerHeight);
            }

            // Animation Loop
            function animate() {
                requestAnimationFrame(animate);
                controls.update();
                renderer.render(scene, camera);
            }
            animate();
        });

        // Turntable logic
        document.addEventListener('DOMContentLoaded', () => {
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
                        model.rotation.y = 0; // reset
                    }
                });
            });
        });
    </script>

</body>
</html>
