@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manajemen User</h1>
            <p class="text-gray-500 text-sm mt-0.5">{{ $users->count() }} user terdaftar</p>
        </div>
        <a href="{{ route('users.create') }}"
           class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow">
            + Tambah User
        </a>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        @if($users->isEmpty())
            <div class="py-16 text-center text-gray-400">
                <p class="font-medium">Belum ada user.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">#</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Username</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Role</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Dibuat</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-5 py-3 font-medium text-gray-800">
                            {{ $user->username }}
                            @if($user->id === auth()->id())
                                <span class="ml-1 text-xs text-gray-400">(Anda)</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if($user->role === 'superadmin')
                                <span class="bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded-full">Superadmin</span>
                            @else
                                <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-1 rounded-full">Admin</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('users.edit', $user->id) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user->id) }}"
                                      onsubmit="return confirm('Hapus user {{ $user->username }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-800 text-xs font-medium">
                                        Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
