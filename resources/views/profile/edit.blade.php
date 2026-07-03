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

<div class="profile-tabs">
    <button class="profile-tab active" data-target="tab-uploads">My Uploads</button>
    <button class="profile-tab" data-target="tab-settings">Account Settings</button>
</div>

<div id="tab-uploads" class="profile-tab-content active">
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
</div>

<div id="tab-settings" class="profile-tab-content" style="display: none;">
    <div class="settings-container">
        <div class="form-card settings-card">
            <h2 class="settings-title">Profile Information</h2>
            <p class="settings-desc">Update your account's profile information and email address.</p>
            
            <form method="post" action="{{ route('profile.update') }}" class="auth-form">
                @csrf
                @method('patch')
                
                <div class="form-group">
                    <label for="name" class="form-label">Name</label>
                    <input id="name" name="name" type="text" class="form-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" class="form-input" value="{{ old('email', $user->email) }}" required autocomplete="username">
                    @error('email')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="settings-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    @if (session('status') === 'profile-updated')
                        <span class="settings-saved">Saved.</span>
                    @endif
                </div>
            </form>
        </div>

        <div class="form-card settings-card">
            <h2 class="settings-title">Update Password</h2>
            <p class="settings-desc">Ensure your account is using a long, random password to stay secure.</p>
            
            <form method="post" action="{{ route('password.update') }}" class="auth-form">
                @csrf
                @method('put')
                
                <div class="form-group">
                    <label for="update_password_current_password" class="form-label">Current Password</label>
                    <input id="update_password_current_password" name="current_password" type="password" class="form-input" autocomplete="current-password">
                    @error('current_password', 'updatePassword')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="update_password_password" class="form-label">New Password</label>
                    <input id="update_password_password" name="password" type="password" class="form-input" autocomplete="new-password">
                    @error('password', 'updatePassword')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-input" autocomplete="new-password">
                    @error('password_confirmation', 'updatePassword')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="settings-actions">
                    <button type="submit" class="btn-primary">Save Password</button>
                    @if (session('status') === 'password-updated')
                        <span class="settings-saved">Saved.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab Logic
    const tabs = document.querySelectorAll('.profile-tab');
    const contents = document.querySelectorAll('.profile-tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.style.display = 'none');
            
            this.classList.add('active');
            document.getElementById(this.dataset.target).style.display = 'block';
        });
    });

    // Check if we need to show settings tab due to validation errors or success messages
    if(document.querySelector('.settings-card .form-error') || document.querySelector('.settings-saved')) {
        document.querySelector('[data-target="tab-settings"]').click();
    }

    // Asset AJAX Logic
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
