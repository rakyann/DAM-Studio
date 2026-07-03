@extends('layouts.app')
@section('title', 'Library — DAM Studio')

@section('content')

@if($featuredAssets->isNotEmpty())
<div class="hero-bento">
    @php
        $largeFeatured = $featuredAssets->first();
        $smallFeatured = $featuredAssets->skip(1)->take(2);
    @endphp
    
    <a href="{{ route('assets.show', $largeFeatured) }}" class="bento-card bento-large">
        @if($largeFeatured->thumbnail_path)
            <img src="{{ asset('storage/' . $largeFeatured->thumbnail_path) }}" class="bento-img" alt="{{ $largeFeatured->title }}">
        @else
            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: 700; color: var(--surface-tile-2); text-transform: uppercase;">.{{ $largeFeatured->original_extension }}</div>
        @endif
        <div class="bento-overlay"></div>
        <div class="bento-content">
            <h2 class="bento-title">{{ $largeFeatured->title }}</h2>
            <div class="bento-meta">
                <span class="bento-author">{{ $largeFeatured->user->name ?? 'User' }}</span>
                <span class="bento-date">{{ $largeFeatured->created_at->format('M d, Y') }}</span>
            </div>
            <div class="bento-badge">{{ strtoupper($largeFeatured->category ?? '3D') }}</div>
        </div>
    </a>

    @if($smallFeatured->isNotEmpty())
    <div class="bento-sidebar">
        @foreach($smallFeatured as $sAsset)
        <a href="{{ route('assets.show', $sAsset) }}" class="bento-card bento-small">
            @if($sAsset->thumbnail_path)
                <img src="{{ asset('storage/' . $sAsset->thumbnail_path) }}" class="bento-img" alt="{{ $sAsset->title }}">
            @else
                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; color: var(--surface-tile-2); text-transform: uppercase;">.{{ $sAsset->original_extension }}</div>
            @endif
            <div class="bento-overlay"></div>
            <div class="bento-badge-top">{{ strtoupper($sAsset->category ?? '3D') }}</div>
            <div class="bento-content">
                <h3 class="bento-title">{{ $sAsset->title }}</h3>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endif

<div class="section-header">
    <h2 class="section-title">Recent Uploads</h2>
    <p class="section-desc">Browse and download the latest 3D models from our studio community.</p>
</div>

<div class="filter-bar">
    <div class="filter-row">
        <div class="filter-icon">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
        </div>
        <button class="chip active" data-category="">All</button>
        @foreach(['character','environment','prop','vehicle','weapon'] as $cat)
        <button class="chip" data-category="{{ $cat }}">{{ ucfirst($cat) }}</button>
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
<div class="asset-grid" id="assetGrid">
    @include('assets.partials.grid', ['assets' => $assets])
</div>

@if($assets->hasMorePages())
<div class="load-more-container">
    <button id="loadMoreBtn" class="btn-load-more" data-next-page="{{ $assets->nextPageUrl() }}">Load More</button>
</div>
@endif
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const assetGrid = document.getElementById('assetGrid');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const filterButtons = document.querySelectorAll('.filter-row .chip');
    let currentCategory = '';

    function fetchAssets(url, append = false) {
        if(loadMoreBtn) loadMoreBtn.innerText = 'Loading...';
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(append) {
                assetGrid.insertAdjacentHTML('beforeend', data.html);
            } else {
                assetGrid.innerHTML = data.html;
            }

            if (data.next_page) {
                if(!loadMoreBtn) {
                    // Create load more button if it doesn't exist
                    const container = document.createElement('div');
                    container.className = 'load-more-container';
                    container.innerHTML = `<button id="loadMoreBtn" class="btn-load-more" data-next-page="${data.next_page}">Load More</button>`;
                    assetGrid.parentNode.insertBefore(container, assetGrid.nextSibling);
                    // Rebind event listener
                    document.getElementById('loadMoreBtn').addEventListener('click', handleLoadMore);
                } else {
                    loadMoreBtn.dataset.nextPage = data.next_page;
                    loadMoreBtn.style.display = 'inline-flex';
                    loadMoreBtn.innerText = 'Load More';
                }
            } else {
                if(loadMoreBtn) loadMoreBtn.style.display = 'none';
            }
        })
        .catch(err => {
            console.error(err);
            if(loadMoreBtn) loadMoreBtn.innerText = 'Load More';
        });
    }

    function handleLoadMore() {
        const btn = document.getElementById('loadMoreBtn');
        if(!btn || !btn.dataset.nextPage) return;
        fetchAssets(btn.dataset.nextPage, true);
    }

    if(loadMoreBtn) {
        loadMoreBtn.addEventListener('click', handleLoadMore);
    }

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            currentCategory = this.dataset.category;
            const url = `{{ route('assets.index') }}?category=${currentCategory}`;
            fetchAssets(url, false);
        });
    });
});
</script>
@endpush
