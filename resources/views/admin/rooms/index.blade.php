<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Manajemen Ruangan Lab') }}
            </h2>
            <a href="{{ route('rooms.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-semibold shadow-sm transition">
                + Tambah Ruangan
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
                                    <th class="p-4 font-medium rounded-tl-lg rounded-bl-lg">Kode Ruangan</th>
                                    <th class="p-4 font-medium">Nama Ruangan (Lab)</th>
                                    <th class="p-4 font-medium">Lokasi</th>
                                    <th class="p-4 font-medium text-center">Kapasitas</th>
                                    <th class="p-4 font-medium text-center rounded-tr-lg rounded-br-lg">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-100">
                                @forelse($rooms as $room)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 font-bold text-emerald-600">{{ $room->code }}</td>
                                    <td class="p-4 font-semibold text-gray-900">{{ $room->name }}</td>
                                    <td class="p-4 text-gray-600">{{ $room->location ?? '-' }}</td>
                                    <td class="p-4 text-center">
                                        <span class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">
                                            {{ $room->capacity }} Orang
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('rooms.edit', $room->id) }}" class="px-3 py-1 bg-amber-100 text-amber-700 rounded-md hover:bg-amber-200 transition font-medium">Edit</a>
                                            <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus ruangan ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition font-medium">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-500">
                                        Belum ada data ruangan laboratorium.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
