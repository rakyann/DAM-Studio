@extends('layouts.app')
@section('title', 'Upload Asset — DAM Studio')

@section('content')
<div class="upload-page">
    <h1 class="page-heading">Upload 3D Asset</h1>
    <p class="page-sub">File dikonversi otomatis ke .glb dan siap dipreview di browser.</p>

    <div class="form-card">
        <form method="POST" action="{{ route('assets.store') }}" enctype="multipart/form-data" id="uploadForm">
            @csrf

            <div class="form-group">
                <label class="form-label">Nama Asset</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="contoh: Hero Character v3">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-select">
                        <option value="">Pilih kategori</option>
                        @foreach(['character','environment','prop','vehicle','weapon','other'] as $cat)
                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Versi</label>
                    <input type="number" name="version" value="{{ old('version', 1) }}" min="1" class="form-input">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Tags</label>
                <input type="text" name="tags" value="{{ old('tags') }}" class="form-input" placeholder="hero, fantasy, rigged">
                <p class="form-hint">Pisahkan dengan koma</p>
            </div>

            <hr class="form-divider">

            <div class="form-group">
                <label class="form-label">File 3D</label>
                <div class="dropzone" id="dropzone" onclick="document.getElementById('fileInput').click()">
                    <div class="dropzone-icon">
                        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.338-2.32 5.75 5.75 0 011.844 11.095H6.75z"/></svg>
                    </div>
                    <div class="dropzone-title">Drag & drop file di sini</div>
                    <div class="dropzone-sub">atau <em>klik untuk browse</em></div>
                    <div class="dropzone-sub" style="margin-top:6px;">.blend · .fbx · .obj — max 500MB</div>
                </div>
                <input type="file" name="file" id="fileInput" accept=".blend,.fbx,.obj" style="display:none" onchange="handleFile(this)">
                <div id="filePreview" style="display:none" class="file-selected">
                    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    <span id="fileName"></span>
                </div>
                @error('file')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                Upload & Convert
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function handleFile(input) {
    if (input.files[0]) {
        document.getElementById('fileName').textContent = input.files[0].name;
        document.getElementById('filePreview').style.display = 'flex';
    }
}
const dz = document.getElementById('dropzone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag'); });
dz.addEventListener('dragleave', () => dz.classList.remove('drag'));
dz.addEventListener('drop', e => {
    e.preventDefault(); dz.classList.remove('drag');
    const fi = document.getElementById('fileInput');
    fi.files = e.dataTransfer.files;
    handleFile(fi);
});
document.getElementById('uploadForm').addEventListener('submit', () => {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = 'Converting... please wait';
    btn.disabled = true;
});
</script>
@endpush
@endsection