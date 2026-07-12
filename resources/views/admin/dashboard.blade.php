@extends('layouts.app')
@section('title', 'Admin Dashboard — DAM Studio')

@section('content')
    <div class="admin-dashboard">
        <div class="admin-header">
            <h1 class="display-lg">System Administration</h1>
            <p class="lead" style="color: var(--body-muted); font-size: 18px;">Manage users, assets, and conversion tasks.
            </p>
        </div>

        <!-- Stats Bento Grid -->
        <div class="stats-grid">
            <div class="stat-card glass-panel">
                <div class="stat-icon" style="color: var(--primary-on-dark);">
                    <svg viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="stat-info">
                    <h3>Total Users</h3>
                    <div class="stat-value">{{ number_format($totalUsers) }}</div>
                </div>
            </div>

            <div class="stat-card glass-panel">
                <div class="stat-icon" style="color: #10b981;">
                    <svg viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                </div>
                <div class="stat-info">
                    <h3>Total Assets</h3>
                    <div class="stat-value">{{ number_format($totalAssets) }}</div>
                </div>
            </div>

            <div class="stat-card glass-panel">
                <div class="stat-icon" style="color: #ef4444;">
                    <svg viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="stat-info">
                    <h3>Failed Conversions</h3>
                    <div class="stat-value">{{ number_format($failedConversions) }}</div>
                </div>
            </div>
        </div>

        <!-- Asset Management Table -->
        <div class="admin-table-container glass-panel">
            <div class="table-header">
                <h2>All Assets</h2>
                <div class="table-filters">
                    <form action="{{ route('admin.dashboard') }}" method="GET" class="filter-form">
                        <select name="status" class="form-select" style="width: auto; padding: 8px 12px; font-size: 14px;"
                            onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>Queued</option>
                            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing
                            </option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed
                            </option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </form>
                </div>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Title</th>
                        <th>Uploader</th>
                        <th>Status</th>
                        <th>Visibility</th>
                        <th>Uploaded At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr>
                            <td>
                                <div class="table-thumb">
                                    @if($asset->thumbnail_path)
                                        <img src="{{ asset('storage/' . 'public' . $asset->thumbnail_path) }}" alt="Thumb">
                                    @else
                                        <div class="thumb-placeholder">{{ strtoupper($asset->original_extension) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td style="font-weight: 500; color: var(--on-dark);">
                                <a href="{{ route('assets.show', $asset) }}" target="_blank"
                                    class="asset-link">{{ Str::limit($asset->title, 30) }}</a>
                            </td>
                            <td>{{ $asset->user->name ?? 'Unknown' }}</td>
                            <td>
                                <span
                                    class="status-badge status-{{ $asset->status->value }}">{{ ucfirst($asset->status->value) }}</span>
                            </td>
                            <td>
                                <span class="visibility-badge">{{ ucfirst($asset->visibility->value) }}</span>
                            </td>
                            <td>{{ $asset->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <form action="{{ route('admin.assets.toggle-visibility', $asset) }}" method="POST"
                                        style="margin: 0;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-action"
                                            style="white-space: nowrap; padding: 6px 12px; color: {{ $asset->visibility->value === 'public' ? '#f59e0b' : '#10b981' }}; border-color: rgba(255,255,255,0.1); background: rgba(255,255,255,0.05);"
                                            title="Toggle Visibility">
                                            {{ $asset->visibility->value === 'public' ? 'Make Private' : 'Make Public' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.assets.destroy', $asset) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this asset? This cannot be undone.');"
                                        style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action"
                                            style="white-space: nowrap; padding: 6px 12px; color: #ef4444; border-color: rgba(239, 68, 68, 0.2); background: rgba(239, 68, 68, 0.05);"
                                            title="Permanently Delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--body-muted);">No assets found
                                in the system.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="padding: 20px; border-top: 1px solid var(--hairline);">
                {{ $assets->links() }}
            </div>
        </div>
    </div>
@endsection