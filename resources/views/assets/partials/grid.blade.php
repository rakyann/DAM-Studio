@php $thumbs = ['thumb-1', 'thumb-2', 'thumb-3', 'thumb-4', 'thumb-5', 'thumb-6']; @endphp
@foreach($assets as $i => $asset)
    <a href="{{ route('assets.show', $asset) }}" class="asset-card">
        {{-- tess --}}
        <div class="card-thumbnail" style="background: #000; border-radius: var(--rounded-md) var(--rounded-md) 0 0;">
            @if($asset->thumbnail_path)
                <img src="{{ asset('storage/' . 'public/' . $asset->thumbnail_path) }}" alt="{{ $asset->title }}">
            @else
                <div
                    style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--body-muted);">
                    <div style="font-size: 24px; font-weight: 700; color: var(--ink-muted-48); text-transform: uppercase;">
                        .{{ $asset->original_extension }}</div>
                </div>
            @endif
            <div class="turntable-canvas"></div>

            <!-- Dashboard specifics -->
            <div
                style="position: absolute; top: 12px; left: 12px; background: rgba(0,0,0,0.6); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; color: white;">
                {{ strtoupper($asset->category ?? '3D') }}
            </div>
            <div
                style="position: absolute; top: 12px; right: 12px; background: {{ $asset->status === 'completed' ? '#10b981' : ($asset->status === 'failed' ? '#ef4444' : '#f59e0b') }}; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; color: white; text-transform: uppercase;">
                {{ $asset->status }}
            </div>
        </div>
        <div class="card-body">
            <div class="card-title-row">
                <div class="card-name">{{ $asset->title }}</div>
                <svg class="card-arrow" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                </svg>
            </div>
            <div class="card-desc">Polygons: {{ number_format($asset->polygon_count ?: 0) }} • Format:
                {{ strtoupper($asset->original_extension) }} • Size: {{ $asset->formattedFileSize() }}</div>
        </div>
        <div class="card-foot">
            <div class="card-avatar">{{ strtoupper(substr($asset->user->name ?? 'U', 0, 2)) }}</div>
            <div class="card-author">{{ $asset->user->name ?? 'User' }}</div>
            <div class="card-bullet">•</div>
            <div class="card-date">{{ $asset->created_at->format('M d, Y') }}</div>
        </div>
    </a>
@endforeach