<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Staf Administrasi') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen" x-data="{ tab: 'ringkasan' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Navigation Tabs (Clean UI) -->
            <div class="border-b border-gray-200 overflow-x-auto">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="tab = 'ringkasan'" :class="tab === 'ringkasan' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Ringkasan</button>
                    <button @click="tab = 'draf_disetujui'" :class="tab === 'draf_disetujui' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Draf Disetujui</button>
                    <button @click="tab = 'penerimaan_barang'" :class="tab === 'penerimaan_barang' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Penerimaan Barang</button>
                    <button @click="tab = 'update_inventaris'" :class="tab === 'update_inventaris' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Update Inventaris</button>
                </nav>
            </div>

            <!-- TAB 1: RINGKASAN -->
            <div x-show="tab === 'ringkasan'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                
                <!-- Welcome Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-6">
                    <h3 class="text-2xl font-bold text-gray-900">Halo, {{ Auth::user()->name }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Kelola dokumen pengadaan dan pantau status pemesanan barang dari vendor.</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Pengadaan Aktif</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">15</h4>
                        <p class="mt-2 text-sm text-indigo-600 font-medium">Sedang diproses vendor</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Vendor Terdaftar</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">24</h4>
                        <p class="mt-2 text-sm text-gray-500">Vendor Aktif</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Dokumen Tertunda</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">5</h4>
                        <p class="mt-2 text-sm text-amber-600 font-medium">Menunggu PO / Invoice</p>
                    </div>
                </div>
            </div>

            <!-- TAB 2: DRAF DISETUJUI -->
            <div x-show="tab === 'draf_disetujui'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Draf Pengadaan Disetujui</h3>
                        <p class="mt-1 text-sm text-gray-500">Daftar item pengadaan dari Kaprodi yang statusnya sudah final dan siap dibuatkan Purchase Order (PO).</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Draf</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asal Lab</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Barang</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Disetujui</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">DRF-2026-002</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Lab Komputer Dasar</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Monitor 24" IPS Dell</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <button type="button" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Buat Pesanan (PO)
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 3: PENERIMAAN BARANG -->
            <div x-show="tab === 'penerimaan_barang'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Input Penerimaan Barang</h3>
                        <p class="mt-1 text-sm text-gray-500">Catat tanggal penerimaan fisik barang. Sistem mendukung penerimaan secara parsial (bertahap).</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Barang (PO)</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pesanan</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sudah Diterima</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Input Penerimaan Baru</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">Monitor 24" IPS Dell</div>
                                        <div class="text-xs text-gray-500 mt-1">PO-2026-004</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">5</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-medium rounded-full bg-green-100 text-green-800 border border-green-200">
                                            2
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap bg-gray-50">
                                        <form action="#" @submit.prevent class="flex items-center justify-center space-x-2">
                                            <input type="number" value="1" min="1" max="3" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center" placeholder="Qty">
                                            <input type="date" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Catat
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 4: UPDATE INVENTARIS -->
            <div x-show="tab === 'update_inventaris'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Update Data Inventaris (Pelabelan)</h3>
                        <p class="mt-1 text-sm text-gray-500">Masukkan barang yang sudah diterima ke dalam buku inventaris resmi. Pasang label dan foto QR Code/Barcode.</p>
                    </div>
                    
                    <div class="p-6">
                        <form action="#" @submit.prevent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Pilih Barang Masuk (Belum Berlabel)</label>
                                    <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                                        <option>Monitor 24" IPS Dell (Diterima: 25 Mei 2026)</option>
                                        <option>Monitor 24" IPS Dell (Diterima: 27 Mei 2026)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Penomoran Label Inventaris</label>
                                    <input type="text" placeholder="Contoh: INV-KOM-001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <p class="mt-2 text-xs text-gray-500">Sistem menyarankan: INV-KOM-045</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Lokasi Penempatan Awal</label>
                                    <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                                        <option>Lab Komputer Dasar (Gedung A)</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Foto Aset / Label (QR)</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 transition-colors">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span>Upload file</span>
                                                    <input id="file-upload" name="file-upload" type="file" class="sr-only">
                                                </label>
                                                <p class="pl-1">atau drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">PNG, JPG up to 5MB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Simpan Data Inventaris
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
