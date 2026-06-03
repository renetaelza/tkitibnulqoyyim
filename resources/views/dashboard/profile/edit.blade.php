@extends('layouts.dashboard')

@section('title', 'Profil')
@section('page_title', 'Profil')

@section('content')
    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <h2 style="margin-bottom: 12px;">Profil</h2>

        @if (session('status') === 'profile-updated')
            <div style="margin-bottom: 12px; color: var(--green-dark); font-weight: 700;">Profil berhasil diperbarui.</div>
        @elseif (session('status') === 'password-updated')
            <div style="margin-bottom: 12px; color: var(--green-dark); font-weight: 700;">Password berhasil diperbarui.</div>
        @endif

        <h3 style="margin: 16px 0 10px;">Update Profil</h3>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('patch')

            <div class="form-group">
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <span style="color: #FF6B35; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <span style="color: #FF6B35; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit">Simpan</button>
        </form>

        <hr style="margin: 22px 0;" />

        <h3 style="margin: 0 0 10px;">Update Password</h3>
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('put')

            <div class="form-group">
                <input type="password" name="current_password" placeholder="Password Saat Ini" required>
                @error('current_password', 'updatePassword')
                    <span style="color: #FF6B35; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="Password Baru" required>
                @error('password', 'updatePassword')
                    <span style="color: #FF6B35; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <input type="password" name="password_confirmation" placeholder="Konfirmasi Password Baru" required>
            </div>

            <button type="submit">Update Password</button>
        </form>

        <hr style="margin: 22px 0;" />

        <h3 style="margin: 0 0 10px;">Hapus Akun</h3>
        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <div class="form-group">
                <input type="password" name="password" placeholder="Konfirmasi Password" required>
                @error('password', 'userDeletion')
                    <span style="color: #FF6B35; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" style="background: #fff; color: #b91c1c; border: 2px solid #fecaca; box-shadow: none;">Hapus Akun</button>
        </form>
    </div>
@endsection
