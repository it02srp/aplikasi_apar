@extends('layouts.app')

@section('title', 'Edit User — ' . $user->username)

@section('content')
<div class="max-w-lg mx-auto space-y-5">
    <div>
        <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-1">Edit User — <span class="font-normal">{{ $user->username }}</span></h1>
    </div>

    <form method="POST" action="{{ route('users.update', $user->id) }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 @error('username') border-red-500 @enderror">
                @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Password Baru
                    <span class="text-gray-400 font-normal">(kosongkan jika tidak ingin mengubah)</span>
                </label>
                <input type="password" name="password"
                    placeholder="Minimal 6 karakter"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 @error('password') border-red-500 @enderror">
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation"
                    placeholder="Ulangi password baru"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                <select name="role"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 @error('role') border-red-500 @enderror">
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                    <option value="superadmin" @selected(old('role', $user->role) === 'superadmin')>Superadmin</option>
                </select>
                @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="bg-green-700 hover:bg-green-800 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow">
                    Perbarui User
                </button>
                <a href="{{ route('users.index') }}"
                   class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-2.5 rounded-lg text-sm font-semibold transition">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
