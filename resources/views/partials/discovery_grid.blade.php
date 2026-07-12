@if($assets->count() > 0)
    <div class="grid-container" style="gap: 32px;">
        @foreach($assets as $asset)
            <div class="asset-card glass-panel hover-scale"
                style="background: var(--surface-tile-1); border: 1px solid rgba(255,255,255,0.05);"
                data-glb="{{ $asset->viewer_glb_path ? asset('storage/' . $asset->viewer_glb_path) : '' }}">
                <div class="card-thumbnail" style="background: #000; border-radius: var(--rounded-md) var(--rounded-md) 0 0;">
                    @if($asset->thumbnail_path)
                        <img src="{{ asset('storage/' . 'public' . $asset->thumbnail_path) }}" alt="{{ $asset->title }}"
                            style="transition: opacity 0.3s;">
                    @else
                        <div
                            style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--body-muted);">
                            <svg viewBox="0 0 24 24" style="width: 48px; height: 48px; opacity: 0.5;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                    <div class="turntable-canvas"></div>
                </div>
                <div class="card-info" style="padding: 20px;">
                    <h3 class="body-strong" style="color: var(--on-dark); font-size: 18px;">{{ $asset->title }}</h3>
                    <p class="caption" style="color: var(--body-muted); margin-bottom: 16px;">By
                        {{ $asset->user->name ?? 'Unknown' }}
                    </p>
                    <div class="card-actions" style="display: flex; gap: 12px;">
                        <button class="btn-action restricted-action" data-action="download"
                            style="flex: 1; background: rgba(255,255,255,0.1); border: none; color: white; border-radius: var(--rounded-sm); padding: 8px;">Download</button>
                        <button class="btn-action restricted-action" data-action="like"
                            style="flex: 1; background: rgba(255,255,255,0.1); border: none; color: white; border-radius: var(--rounded-sm); padding: 8px;">Like</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div style="margin-top: 60px; display: flex; justify-content: center;">
        {{ $assets->links() }}
    </div>
@else
    <div class="empty-state glass-panel"
        style="max-width: 600px; margin: 0 auto; padding: 60px; border-radius: var(--rounded-lg);">
        <svg viewBox="0 0 24 24" style="width: 64px; height: 64px; color: var(--ink-muted-48); margin-bottom: 24px;">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <h3 class="lead" style="color: var(--on-dark); margin-bottom: 8px;">No assets found</h3>
        <p style="color: var(--body-muted);">Check back later or adjust your filters.</p>
    </div>
@endif