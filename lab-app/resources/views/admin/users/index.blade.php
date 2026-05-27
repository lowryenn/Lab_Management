<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Manajemen Pengguna') }}
            </h2>
            <a href="{{ route('users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold shadow-sm transition">
                + Tambah Pengguna
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-sm">
                                    <th class="p-4 font-medium rounded-tl-lg rounded-bl-lg">Nama</th>
                                    <th class="p-4 font-medium">Email</th>
                                    <th class="p-4 font-medium">Role</th>
                                    <th class="p-4 font-medium text-center rounded-tr-lg rounded-br-lg">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-100">
                                @foreach($users as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 font-semibold text-gray-900">{{ $user->name }}</td>
                                    <td class="p-4 text-gray-600">{{ $user->email }}</td>
                                    <td class="p-4">
                                        <span class="px-2.5 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold capitalize">
                                            {{ str_replace('_', ' ', $user->role) }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('users.edit', $user->id) }}" class="px-3 py-1 bg-amber-100 text-amber-700 rounded-md hover:bg-amber-200 transition font-medium">Edit</a>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition font-medium">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
