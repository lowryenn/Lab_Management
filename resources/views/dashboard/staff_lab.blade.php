<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Staf Laboratorium') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen" x-data="{ tab: '{{ request('tab', 'ringkasan') }}', showAddBhp: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
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
            <div class="border-b border-gray-200 overflow-x-auto">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="tab = 'ringkasan'" :class="tab === 'ringkasan' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Ringkasan</button>
                    <button @click="tab = 'kelola_stok'" :class="tab === 'kelola_stok' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Kelola Stok BHP</button>
                    <button @click="tab = 'log_maintenance'" :class="tab === 'log_maintenance' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Log Maintenance & Kondisi</button>
                </nav>
            </div>

            <!-- TAB 1: RINGKASAN -->
            <div x-show="tab === 'ringkasan'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                
                <!-- Welcome Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-6">
                    <h3 class="text-2xl font-bold text-gray-900">Halo, {{ Auth::user()->name }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Kelola operasional lab, inventaris, dan barang habis pakai (BHP) hari ini.</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Total Aset</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">{{ $totalAssets }}</h4>
                        <p class="mt-2 text-sm text-gray-500">Dalam tanggung jawab Anda</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">BHP Menipis</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">{{ $bhpLow }}</h4>
                        <p class="mt-2 text-sm {{ $bhpLow > 0 ? 'text-amber-600 font-medium' : 'text-gray-500' }}">
                            {{ $bhpLow > 0 ? 'Perlu Pengajuan Baru' : 'Semua stok aman' }}
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Log Maintenance Minggu Ini</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">{{ $maintenanceDue }}</h4>
                        <p class="mt-2 text-sm text-gray-500">Catatan pemeliharaan</p>
                    </div>
                </div>
            </div>

            <!-- TAB 2: KELOLA STOK BHP -->
            <div x-show="tab === 'kelola_stok'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Manajemen Barang Habis Pakai (BHP)</h3>
                            <p class="mt-1 text-sm text-gray-500">Update stok BHP secara manual jika ada barang yang masuk atau terpakai di luar maintenance.</p>
                        </div>
                        <button type="button" @click="showAddBhp = !showAddBhp" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Tambah BHP Baru
                        </button>
                    </div>

                    {{-- Add BHP Form --}}
                    <div x-show="showAddBhp" x-transition class="p-6 border-b border-gray-200 bg-indigo-50">
                        <form action="{{ route('staff_lab.bhp.store') }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama BHP</label>
                                    <input type="text" name="name" required placeholder="Contoh: Alkohol 70%" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Stok Awal</label>
                                    <input type="number" name="stock" required min="0" value="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Satuan</label>
                                    <input type="text" name="unit" required placeholder="Liter, Rim, Tube..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Stok Minimum</label>
                                    <input type="number" name="min_stock" min="0" value="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                    Simpan BHP
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama BHP</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Min. Stok</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Update Stok Baru</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($bhpItems as $bhp)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $bhp->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium {{ $bhp->stock <= $bhp->min_stock ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $bhp->stock }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">{{ $bhp->unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">{{ $bhp->min_stock }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap bg-gray-50">
                                        <form action="{{ route('staff_lab.bhp.update-stock', $bhp) }}" method="POST" class="flex justify-center items-center space-x-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" name="stock" value="{{ $bhp->stock }}" min="0" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        Belum ada data BHP. Klik "Tambah BHP Baru" untuk menambahkan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 3: LOG MAINTENANCE -->
            <div x-show="tab === 'log_maintenance'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Input Log Pemeliharaan (Maintenance)</h3>
                        <p class="mt-1 text-sm text-gray-500">Catat histori perawatan aset inventaris. Jika pemeliharaan membutuhkan barang habis pakai, sistem akan memotong stok BHP secara otomatis.</p>
                    </div>
                    
                    <div class="p-6">
                        <form action="{{ route('staff_lab.maintenance.store') }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Pilih Aset Inventaris</label>
                                    <select name="inventory_item_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                                        <option value="">-- Pilih Aset --</option>
                                        @foreach($inventoryItems as $item)
                                            <option value="{{ $item->id }}" {{ old('inventory_item_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->label_code ?? 'Tanpa Label' }} - {{ $item->name }} ({{ $item->condition_label }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tanggal Pemeliharaan</label>
                                    <input type="date" name="maintenance_date" required value="{{ old('maintenance_date', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Update Kondisi Akhir</label>
                                    <select name="condition_after" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                                        <option value="baik" {{ old('condition_after') == 'baik' ? 'selected' : '' }}>Baik (Normal)</option>
                                        <option value="kurang_baik" {{ old('condition_after') == 'kurang_baik' ? 'selected' : '' }}>Kurang Baik (Butuh Perbaikan Ringan)</option>
                                        <option value="rusak_berat" {{ old('condition_after') == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat (Tidak Bisa Digunakan)</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Detail Kegiatan Pemeliharaan</label>
                                    <textarea name="description" rows="3" placeholder="Jelaskan tindakan yang dilakukan..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                                </div>

                                <!-- BHP Usage Section -->
                                <div class="md:col-span-2 bg-gray-50 p-4 border border-gray-200 rounded-md" x-data="{ pakaiBhp: {{ old('uses_bhp') ? 'true' : 'false' }} }">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="hidden" name="uses_bhp" value="0">
                                            <input type="checkbox" name="uses_bhp" value="1" x-model="pakaiBhp" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label class="font-medium text-gray-700">Pemeliharaan ini menggunakan Barang Habis Pakai (BHP)</label>
                                        </div>
                                    </div>
                                    
                                    <div x-show="pakaiBhp" class="mt-4 pl-7" style="display: none;">
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-medium text-gray-500 uppercase">Pilih BHP</label>
                                                <select name="bhp_item_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                                                    <option value="">-- Pilih BHP --</option>
                                                    @foreach($bhpItems as $bhp)
                                                        <option value="{{ $bhp->id }}" {{ old('bhp_item_id') == $bhp->id ? 'selected' : '' }}>
                                                            {{ $bhp->name }} (Sisa: {{ $bhp->stock }} {{ $bhp->unit }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase">Jumlah Terpakai</label>
                                                <input type="number" name="bhp_qty_used" value="{{ old('bhp_qty_used', 1) }}" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">Stok BHP yang dipilih akan otomatis terpotong saat form ini disimpan.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <button type="reset" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Batal</button>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Simpan Log Pemeliharaan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
