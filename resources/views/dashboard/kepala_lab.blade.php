<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Kepala Laboratorium') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen" x-data="{ tab: 'ringkasan' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
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
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">12</h4>
                        <p class="mt-2 text-sm text-gray-500">Pengajuan Pengadaan & BHP</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Nilai Aset Lab</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">Rp 425M</h4>
                        <p class="mt-2 text-sm text-emerald-600 font-medium">↑ Bertambah 5% bulan ini</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Perlu Perbaikan</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">3</h4>
                        <p class="mt-2 text-sm text-rose-600 font-medium">Alat dalam masa pemeliharaan</p>
                    </div>
                </div>
            </div>

            <!-- TAB 2: BUAT DRAF PENGADAAN -->
            <div x-show="tab === 'buat_draf'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Buat Draf Pengadaan Barang (Tahunan)</h3>
                        <p class="mt-1 text-sm text-gray-500">Masukkan data inventaris dan BHP yang akan dibeli pada periode ini.</p>
                    </div>

                    <div class="p-6">
                        <form action="#" method="POST" @submit.prevent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                                    <input type="text" placeholder="Contoh: Mikroskop Digital" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Estimasi Harga Satuan</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jumlah Barang</label>
                                    <input type="number" value="1" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Link Pembelian (Referensi e-Katalog / Vendor)</label>
                                    <input type="url" placeholder="https://" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <div class="md:col-span-2 bg-gray-50 p-4 border border-gray-200 rounded-md" x-data="{ isReplacing: false }">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" x-model="isReplacing" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label class="font-medium text-gray-700">Barang ini menggantikan aset inventaris yang sudah ada</label>
                                            <p class="text-gray-500">Pilih opsi ini jika pengadaan ini untuk mengganti aset yang rusak atau usang.</p>
                                        </div>
                                    </div>
                                    <div x-show="isReplacing" class="mt-4 pl-7" style="display: none;">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Barang yang Digantikan</label>
                                        <select class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                                            <option value="">-- Pilih Aset --</option>
                                            <option value="1">INV-2021-042 - Mikroskop Analog (Kondisi: Rusak Berat)</option>
                                            <option value="2">INV-2019-011 - PC Server (Kondisi: Usang)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <button type="button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Batal</button>
                                <button type="button" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Simpan ke Draf</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB 3: RIWAYAT DRAF -->
            <div x-show="tab === 'riwayat_draf'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Riwayat Draf Pengadaan</h3>
                        <input type="text" placeholder="Cari Draf..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm w-64">
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Draf</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Item</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Estimasi Harga</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">DRF-2026-001</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">12 Mei 2026</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5 Item</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rp 45.000.000</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-medium rounded-full bg-gray-100 text-gray-800 border border-gray-200">
                                            Locked (Final)
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <button class="text-gray-400 cursor-not-allowed mx-2" disabled>Edit</button>
                                        <button class="text-indigo-600 hover:text-indigo-900 mx-2">Detail</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">DRF-2026-002</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">27 Mei 2026</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">2 Item</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rp 12.500.000</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-medium rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                            Draft (Bisa diedit)
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <button class="text-indigo-600 hover:text-indigo-900 mx-2">Edit</button>
                                        <button class="text-indigo-600 hover:text-indigo-900 mx-2">Detail</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
