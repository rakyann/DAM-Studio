@extends('layouts.app')
@section('title', $asset->name . ' — DAM Studio')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('assets.index') }}">Library</a>
    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
    <span>{{ $asset->name }}</span>
</div>

<div class="detail-grid">

    <div class="viewer-wrap">
        @if($asset->isReady())
            <canvas id="three-canvas"></canvas>
            <div id="viewer-loading" class="viewer-loading">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                <span id="loading-text">Memuat model...</span>
            </div>
            <div class="viewer-hint-bar">Drag: rotate · Scroll: zoom · Right drag: pan</div>
            <button class="viewer-fs-btn" onclick="toggleFullscreen()">⛶ Fullscreen</button>
        @elseif($asset->status === 'processing')
            <div class="viewer-loading">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                <span>Sedang dikonversi...</span>
            </div>
        @else
            <div class="viewer-loading">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                <span>{{ $asset->status === 'failed' ? 'Konversi gagal' : 'Pending...' }}</span>
            </div>
        @endif
    </div>

    <div class="info-card">
        <div class="info-name">{{ $asset->name }}</div>
        <div class="info-version">v{{ $asset->version }}</div>

        <div class="meta-row"><span class="meta-key">Kategori</span><span class="meta-val">{{ $asset->category ?? '—' }}</span></div>
        <div class="meta-row"><span class="meta-key">Format asli</span><span class="meta-val">.{{ $asset->original_extension }}</span></div>
        <div class="meta-row"><span class="meta-key">Ukuran</span><span class="meta-val">{{ $asset->formattedFileSize() }}</span></div>
        <div class="meta-row">
            <span class="meta-key">Status</span>
            <span class="card-status s-{{ $asset->status }}">{{ $asset->status }}</span>
        </div>
        <div class="meta-row"><span class="meta-key">Diupload</span><span class="meta-val">{{ $asset->created_at->format('d M Y') }}</span></div>

        @if($asset->tags && count($asset->tags))
        <hr class="info-divider">
        <div class="tags-row">
            @foreach($asset->tags as $tag)
            <span class="tag-pill">{{ $tag }}</span>
            @endforeach
        </div>
        @endif

        <hr class="info-divider">

        @if($asset->isReady())
        <a href="{{ route('assets.download', $asset) }}" class="btn-dl">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Download ({{ strtoupper($asset->original_extension) }})
        </a>
        @endif

        <form method="POST" action="{{ route('assets.destroy', $asset) }}" onsubmit="return confirm('Hapus asset ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-del">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                Hapus Asset
            </button>
        </form>
    </div>
</div>

@if($asset->isReady())
@push('scripts')
<script type="importmap">
{"imports":{"three":"https://cdn.jsdelivr.net/npm/three@0.165.0/build/three.module.js","three/addons/":"https://cdn.jsdelivr.net/npm/three@0.165.0/examples/jsm/"}}
</script>
<script type="module">
import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

const wrap    = document.querySelector('.viewer-wrap');
const canvas  = document.getElementById('three-canvas');
const overlay = document.getElementById('viewer-loading');
const loadTxt = document.getElementById('loading-text');

const scene    = new THREE.Scene();
scene.background = new THREE.Color(0x1a1a2e);

const camera = new THREE.PerspectiveCamera(45, wrap.clientWidth / wrap.clientHeight, 0.01, 1000);
camera.position.set(0, 0, 5);

const renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
renderer.setSize(wrap.clientWidth, wrap.clientHeight);
renderer.setPixelRatio(window.devicePixelRatio);
renderer.outputColorSpace = THREE.SRGBColorSpace;

scene.add(new THREE.AmbientLight(0xffffff, 0.7));
const dir = new THREE.DirectionalLight(0xffffff, 1.2);
dir.position.set(5, 10, 7.5);
scene.add(dir);
const fill = new THREE.DirectionalLight(0xffffff, 0.3);
fill.position.set(-5, 0, -5);
scene.add(fill);

const controls = new OrbitControls(camera, renderer.domElement);
controls.enableDamping = true;
controls.dampingFactor = 0.05;

new GLTFLoader().load(
    '{{ $viewerUrl }}',
    gltf => {
        const model = gltf.scene;
        scene.add(model);
        const box    = new THREE.Box3().setFromObject(model);
        const center = box.getCenter(new THREE.Vector3());
        const size   = box.getSize(new THREE.Vector3());
        model.position.sub(center);
        const maxDim = Math.max(size.x, size.y, size.z);
        camera.position.z = Math.abs(maxDim / Math.sin(camera.fov * Math.PI / 360)) * 1.2;
        controls.maxDistance = camera.position.z * 3;
        controls.update();
        overlay.style.display = 'none';
    },
    p => { if (p.total > 0) loadTxt.textContent = `Memuat... ${Math.round(p.loaded/p.total*100)}%`; },
    () => { loadTxt.textContent = '❌ Gagal memuat model'; }
);

window.addEventListener('resize', () => {
    camera.aspect = wrap.clientWidth / wrap.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(wrap.clientWidth, wrap.clientHeight);
});

(function animate() { requestAnimationFrame(animate); controls.update(); renderer.render(scene, camera); })();

window.toggleFullscreen = () => {
    document.fullscreenElement ? document.exitFullscreen() : wrap.requestFullscreen();
};
</script>
@endpush
@endif

@endsection