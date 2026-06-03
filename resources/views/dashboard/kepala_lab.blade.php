<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Kepala Laboratorium') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen" x-data="{ tab: '{{ request('tab', 'ringkasan') }}', showAddItem: false, selectedDraftId: null }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <!-- Navigation Tabs (Clean UI) -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="tab = 'ringkasan'" :class="tab === 'ringkasan' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Ringkasan</button>
                    <button @click="tab = 'buat_draf'" :class="tab === 'buat_draf' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Buat Draf Pengadaan</button>
                    <button @click="tab = 'riwayat_draf'" :class="tab === 'riwayat_draf' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Riwayat Draf</button>
                </nav>
            </div>

            <!-- TAB 1: RINGKASAN -->
            <div x-show="tab === 'ringkasan'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                
                <!-- Welcome Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-6">
                    <h3 class="text-2xl font-bold text-gray-900">Halo, {{ Auth::user()->name }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Pantau kondisi aset dan setujui pengajuan pengadaan laboratorium Anda.</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Menunggu Persetujuan</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">{{ $pendingApprovals }}</h4>
                        <p class="mt-2 text-sm text-gray-500">Pengajuan Pengadaan & BHP</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Nilai Aset Lab</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</h4>
                        <p class="mt-2 text-sm text-emerald-600 font-medium">Total inventaris</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Perlu Perbaikan</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">{{ $needsRepair }}</h4>
                        <p class="mt-2 text-sm {{ $needsRepair > 0 ? 'text-rose-600 font-medium' : 'text-gray-500' }}">
                            {{ $needsRepair > 0 ? 'Alat dalam masa pemeliharaan' : 'Semua aset baik' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- TAB 2: BUAT DRAF PENGADAAN -->
            <div x-show="tab === 'buat_draf'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                
                {{-- Step 1: Create New Draft --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Buat Draf Pengadaan Baru</h3>
                        <p class="mt-1 text-sm text-gray-500">Buat draf baru terlebih dahulu, lalu tambahkan item-item barang ke dalamnya.</p>
                    </div>

                    <div class="p-6">
                        <form action="{{ route('kepala_lab.drafts.store') }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Lab Tujuan (Opsional)</label>
                                    <select name="room_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                                        <option value="">-- Pilih Lab --</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->name }} ({{ $room->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Buat Draf Baru
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Step 2: Add Items to existing draft --}}
                @php $editableDrafts = $drafts->where('status', 'draft'); @endphp
                @if($editableDrafts->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Tambah Item ke Draf yang Ada</h3>
                        <p class="mt-1 text-sm text-gray-500">Masukkan data inventaris dan BHP yang akan dibeli pada periode ini.</p>
                    </div>

                    <div class="p-6">
                        @foreach($editableDrafts as $draft)
                        <div class="mb-6 pb-6 border-b border-gray-100 last:border-0 last:mb-0 last:pb-0">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-base font-semibold text-indigo-600">{{ $draft->draft_number }} 
                                    @if($draft->room) <span class="text-gray-400 text-sm font-normal">— {{ $draft->room->name }}</span> @endif
                                </h4>
                                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">Draft (Bisa diedit)</span>
                            </div>

                            {{-- Existing items in this draft --}}
                            @if($draft->items->count() > 0)
                            <div class="mb-4 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($draft->items as $item)
                                        <tr>
                                            <td class="px-4 py-2 font-medium text-gray-900">{{ $item->name }}</td>
                                            <td class="px-4 py-2 text-center">{{ $item->quantity }}</td>
                                            <td class="px-4 py-2 text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-right font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-center">
                                                <form action="{{ route('kepala_lab.draft-items.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Hapus item ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-medium">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 text-right font-semibold text-gray-700">Total Estimasi:</td>
                                            <td class="px-4 py-2 text-right font-bold text-gray-900">Rp {{ number_format($draft->total_price, 0, ',', '.') }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            @endif

                            {{-- Add new item form --}}
                            <form action="{{ route('kepala_lab.drafts.items.store', $draft) }}" method="POST" class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                @csrf
                                <p class="text-sm font-medium text-gray-700 mb-3">Tambah Item Baru:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                                        <input type="text" name="name" required placeholder="Contoh: Mikroskop Digital" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Estimasi Harga Satuan</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                            </div>
                                            <input type="number" name="price" required min="0" class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Jumlah Barang</label>
                                        <input type="number" name="quantity" required value="1" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Link Pembelian (Referensi e-Katalog / Vendor)</label>
                                        <input type="url" name="link" placeholder="https://" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <div class="md:col-span-2" x-data="{ isReplacing: false }">
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" x-model="isReplacing" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label class="font-medium text-gray-700">Barang ini menggantikan aset inventaris yang sudah ada</label>
                                            </div>
                                        </div>
                                        <div x-show="isReplacing" class="mt-3 pl-7" style="display: none;">
                                            <select name="replaces_inventory_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                                                <option value="">-- Pilih Aset --</option>
                                                @foreach($inventoryItems as $inv)
                                                    <option value="{{ $inv->id }}">{{ $inv->label_code ?? 'No Label' }} - {{ $inv->name }} (Kondisi: {{ $inv->condition_label }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700">
                                        Tambah Item ke Draf
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                    <p class="text-gray-500">Belum ada draf yang bisa diedit. Buat draf baru di atas terlebih dahulu.</p>
                </div>
                @endif
            </div>

            <!-- TAB 3: RIWAYAT DRAF -->
            <div x-show="tab === 'riwayat_draf'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Riwayat Draf Pengadaan</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Draf</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Item</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Estimasi Harga</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($drafts as $draft)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">{{ $draft->draft_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $draft->created_at->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $draft->room->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $draft->items->count() }} Item</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rp {{ number_format($draft->total_price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($draft->status === 'locked')
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-medium rounded-full bg-gray-100 text-gray-800 border border-gray-200">
                                                Locked (Final)
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-medium rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                Draft (Bisa diedit)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        @if($draft->status === 'draft')
                                            <form action="{{ route('kepala_lab.drafts.destroy', $draft) }}" method="POST" class="inline" onsubmit="return confirm('Hapus draf ini beserta seluruh itemnya?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-900 mx-2">Hapus</button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 mx-2">Terkunci</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        Belum ada draf pengadaan. Klik tab "Buat Draf Pengadaan" untuk memulai.
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
