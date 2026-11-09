
@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')

<div class="edit-profile-page">

    <div class="edit-profile-header">
        <a href="{{ route('profile') }}" class="back-link">
            ← Kembali
        </a>

        <h2>Edit profil</h2>

        <p>Atur informasi profil Hoshist kamu.</p>
    </div>

    @if (session('success'))
        <div class="edit-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="edit-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form
        action="{{ route('profile.update') }}"
        method="POST"
        enctype="multipart/form-data"
        class="edit-profile-form"
    >

        @csrf
        @method('PUT')

        
        <div class="edit-field">

            <label>Banner profil</label>

            <div class="banner-preview">
                @if ($user->banner_path)
                    <img
                        id="bannerPreview"
                        src="{{ Storage::url($user->banner_path) }}"
                        alt="Banner profil"
                    >
                @else
                    <div id="bannerPlaceholder">
                        Belum ada banner
                    </div>

                    <img id="bannerPreview" alt="Preview banner" hidden>
                @endif
            </div>

            <input
                type="file"
                name="banner"
                id="bannerInput"
                accept="image/*"
            >

            @if ($user->banner_path)
                <label class="remove-option">
                    <input
                        type="checkbox"
                        name="remove_banner"
                        value="1"
                    >
                    Hapus banner saat ini
                </label>
            @endif

        </div>


        
        <div class="edit-field">

            <label>Foto profil</label>

            <div class="avatar-preview-wrapper">

                @if ($user->avatar_path)
                    <img
                        id="avatarPreview"
                        class="avatar-preview"
                        src="{{ Storage::url($user->avatar_path) }}"
                        alt="Foto profil"
                    >
                @else
                    <div id="avatarPlaceholder" class="avatar-preview">
                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>

                    <img
                        id="avatarPreview"
                        class="avatar-preview"
                        alt="Preview avatar"
                        hidden
                    >
                @endif

            </div>

            <input
                type="file"
                name="avatar"
                id="avatarInput"
                accept="image/*"
            >

            @if ($user->avatar_path)
                <label class="remove-option">
                    <input
                        type="checkbox"
                        name="remove_avatar"
                        value="1"
                    >
                    Hapus foto profil saat ini
                </label>
            @endif

        </div>


        
        <div class="edit-field">

            <label for="name">Nama pengguna</label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $user->name) }}"
                maxlength="100"
                required
            >

        </div>


       
        <div class="edit-field">

            <label for="username">Username</label>

            <div class="username-input">
                <span>@</span>

                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username', $user->username) }}"
                    maxlength="30"
                    required
                >
            </div>

        </div>


        {{-- BIO --}}
        <div class="edit-field">

            <label for="bio">Bio</label>

            <textarea
                id="bio"
                name="bio"
                maxlength="250"
                rows="4"
                placeholder="Ceritakan sedikit tentang dirimu..."
            >{{ old('bio', $user->bio) }}</textarea>

            <small>Maksimal 250 karakter.</small>

        </div>

        
        <div class="edit-field">

            <label>Genre favorit</label>

            <p class="genre-description">
                Pilih genre yang paling kamu sukai.
                Kamu bisa memilih lebih dari satu!
            </p>

            <div class="genre-selection">

                @foreach ($availableGenres as $genre)

                    <label class="genre-option">

                        <input
                            type="checkbox"
                            name="genre_ids[]"
                            value="{{ $genre->id }}"
                            {{ in_array($genre->id, old('genre_ids', $selectedGenreIds)) ? 'checked' : '' }}
                        >

                        <span class="genre-chip">
                            {{ $genre->name }}
                            <span class="genre-check">✓</span>
                        </span>

                    </label>

                @endforeach

            </div>

            <small>
                Genre yang kamu pilih akan muncul di halaman profilmu.
            </small>

        </div>


        <div class="edit-profile-actions">

            <a href="{{ route('profile') }}" class="cancel-edit">
                Batal
            </a>

            <button type="submit" class="save-profile">
                Simpan perubahan
            </button>

        </div>

    </form>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    function previewImage(inputId, previewId, placeholderId) {

        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);

        if (!input || !preview) return;

        input.addEventListener('change', function () {

            const file = this.files[0];

            if (!file) return;

            if (!file.type.startsWith('image/')) {
                alert('File harus berupa gambar.');
                this.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {

                preview.src = event.target.result;
                preview.hidden = false;

                if (placeholder) {
                    placeholder.hidden = true;
                }

            };

            reader.readAsDataURL(file);

        });

    }

    previewImage('avatarInput', 'avatarPreview', 'avatarPlaceholder');

    previewImage('bannerInput', 'bannerPreview', 'bannerPlaceholder');

});
</script>

@endsection
