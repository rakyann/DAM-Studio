@if($assets->count() > 0)
    <div class="grid-container">
        @foreach($assets as $asset)
            <div class="asset-card" data-glb="{{ $asset->viewer_glb_path ? asset('storage/' . $asset->viewer_glb_path) : '' }}">
                <div class="card-thumbnail">
                    @if($asset->thumbnail_path)
                        <img src="{{ asset('storage/' . $asset->thumbnail_path) }}" alt="{{ $asset->title }}">
                    @else
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--body-muted);">No Thumbnail</div>
                    @endif
                    <div class="turntable-canvas"></div>
                </div>
                <div class="card-info">
                    <h3 class="body-strong">{{ $asset->title }}</h3>
                    <p class="caption">By {{ $asset->user->name ?? 'Unknown' }}</p>
                    <div class="card-actions">
                        <button class="btn-action restricted-action" data-action="download">Download</button>
                        <button class="btn-action restricted-action" data-action="like">Like</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <div style="margin-top: 40px; display: flex; justify-content: center;">
        {{ $assets->links() }}
    </div>
@else
    <div class="empty-state">
        <h3 class="lead">No assets found</h3>
        <p>Check back later or adjust your filters.</p>
    </div>
@endif
