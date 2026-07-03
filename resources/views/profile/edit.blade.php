@extends('layouts.app')
@section('title', 'My Profile — DAM Studio')

@section('content')

<div class="profile-header">
    <div class="profile-avatar-large">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
    <div class="profile-info">
        <h1 class="profile-name">{{ $user->name }}</h1>
        <p class="profile-email">{{ $user->email }}</p>
    </div>
</div>

<div class="section-header">
    <h2 class="section-title">My Uploads</h2>
    <p class="section-desc">Manage and view the 3D assets you have uploaded to the studio.</p>
</div>

@if($assets->isEmpty())
<div class="empty-state">
    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
    <div class="empty-title">You haven't uploaded any assets yet</div>
    <div class="empty-sub">Share your first 3D model with the studio community.</div>
    <a href="{{ route('assets.create') }}" class="btn-primary" style="margin-top: 24px; display: inline-block; text-decoration: none;">+ Upload Asset</a>
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
                    const container = document.createElement('div');
                    container.className = 'load-more-container';
                    container.innerHTML = `<button id="loadMoreBtn" class="btn-load-more" data-next-page="${data.next_page}">Load More</button>`;
                    assetGrid.parentNode.insertBefore(container, assetGrid.nextSibling);
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
});
</script>
@endpush
