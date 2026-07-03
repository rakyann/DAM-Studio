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
        <div class="card-badge">{{ strtoupper($asset->category ?? '3D') }}</div>
        <div class="card-status s-{{ $asset->status }}">{{ $asset->status }}</div>
        <div class="card-overlay"><div class="overlay-btn">Preview →</div></div>
    </div>
    <div class="card-body">
        <div class="card-title-row">
            <div class="card-name">{{ $asset->title }}</div>
            <svg class="card-arrow" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
        </div>
        <div class="card-desc">Polygons: {{ number_format($asset->polygon_count ?: 0) }} • Format: {{ strtoupper($asset->original_extension) }} • Size: {{ $asset->formattedFileSize() }}</div>
    </div>
    <div class="card-foot">
        <div class="card-avatar" style="overflow: hidden;">
            @if($asset->thumbnail_path)
                <img src="{{ asset('storage/' . $asset->thumbnail_path) }}" alt="{{ $asset->user->name ?? 'User' }}" style="width: 100%; height: 100%; object-fit: cover;">
            @else
                {{ strtoupper(substr($asset->user->name ?? 'U', 0, 2)) }}
            @endif
        </div>
        <div class="card-author">{{ $asset->user->name ?? 'User' }}</div>
        <div class="card-bullet">•</div>
        <div class="card-date">{{ $asset->created_at->format('M d, Y') }}</div>
    </div>
</a>
@endforeach
