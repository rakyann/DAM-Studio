@extends('layouts.app')
@section('title', 'Library — DAM Studio')

@section('content')

<div class="hero">
    <div class="hero-tag">
        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        3D Asset Management
    </div>
    <h1 class="hero-title">Your studio's <span>3D asset</span><br>library — all in one place.</h1>
    <p class="hero-sub">Upload, convert, and preview .blend, .fbx, and .obj files directly in the browser.</p>
    <div class="hero-stats">
        <div>
            <div class="hero-stat-val">{{ $assets->total() }}</div>
            <div class="hero-stat-label">Total assets</div>
        </div>
        <div>
            <div class="hero-stat-val">{{ round($assets->sum('file_size') / 1024 / 1024, 1) }} MB</div>
            <div class="hero-stat-label">Storage used</div>
        </div>
        <div>
            <div class="hero-stat-val">{{ $assets->where('status', 'completed')->count() }}</div>
            <div class="hero-stat-label">Converted</div>
        </div>
    </div>
</div>

<div class="toolbar">
    <span class="toolbar-label">Recent uploads</span>
    <div class="filter-row">
        <a href="{{ route('assets.index') }}" class="chip {{ !request('category') ? 'on' : '' }}">All</a>
        @foreach(['character','environment','prop','vehicle','weapon'] as $cat)
        <a href="{{ route('assets.index', ['category' => $cat]) }}" class="chip {{ request('category') === $cat ? 'on' : '' }}">
            {{ ucfirst($cat) }}
        </a>
        @endforeach
    </div>
</div>

@if($assets->isEmpty())
<div class="empty-state">
    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
    <div class="empty-title">No assets yet</div>
    <div class="empty-sub">Upload your first 3D file to get started</div>
    <a href="{{ route('assets.create') }}" class="btn-primary">+ Upload Asset</a>
</div>
@else
<div class="asset-grid">
    @php $thumbs = ['thumb-1','thumb-2','thumb-3','thumb-4','thumb-5','thumb-6']; @endphp
    @foreach($assets as $i => $asset)
    <a href="{{ route('assets.show', $asset) }}" class="asset-card">
        <div class="card-thumb {{ $thumbs[$i % 6] }}">
            <div class="thumb-icon">
                @if($asset->thumbnail_path)
                    <img src="{{ asset('storage/' . $asset->thumbnail_path) }}" alt="{{ $asset->title }}">
                @else
                    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                @endif
            </div>
            <div class="card-overlay"><div class="overlay-btn">Preview →</div></div>
            <span class="card-status s-{{ $asset->status }}">{{ $asset->status }}</span>
        </div>
        <div class="card-body">
            <div class="card-name">{{ $asset->title }}</div>
            <div class="card-meta">
                <span class="card-cat">{{ $asset->category ?? 'Uncategorized' }} · v{{ $asset->version }}</span>
                <span class="card-size">{{ $asset->formattedFileSize() }}</span>
            </div>
        </div>
        <div class="card-foot">
            <span class="card-ext">.{{ $asset->original_extension }}</span>
            <span class="card-date">{{ $asset->created_at->format('d M Y') }}</span>
        </div>
    </a>
    @endforeach
</div>
<div style="margin-top: 24px;">{{ $assets->links() }}</div>
@endif

@endsection
