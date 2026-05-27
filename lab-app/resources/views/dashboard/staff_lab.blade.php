<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Staf Laboratorium') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen" x-data="{ tab: 'ringkasan' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
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
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">342</h4>
                        <p class="mt-2 text-sm text-gray-500">Dalam tanggung jawab Anda</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">BHP Menipis</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">4</h4>
                        <p class="mt-2 text-sm text-amber-600 font-medium">Perlu Pengajuan Baru</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Jadwal Perawatan</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">2</h4>
                        <p class="mt-2 text-sm text-gray-500">Target selesai minggu ini</p>
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
                        <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Tambah BHP Baru
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama BHP</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Update Stok Baru</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Alkohol 70%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-rose-600">1</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">Liter</td>
                                    <td class="px-6 py-4 whitespace-nowrap bg-gray-50">
                                        <form action="#" @submit.prevent class="flex justify-center items-center space-x-2">
                                            <input type="number" value="1" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kertas HVS A4</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-emerald-600">12</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">Rim</td>
                                    <td class="px-6 py-4 whitespace-nowrap bg-gray-50">
                                        <form action="#" @submit.prevent class="flex justify-center items-center space-x-2">
                                            <input type="number" value="12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                </tr>
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
                        <form action="#" @submit.prevent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Pilih Aset Inventaris</label>
                                    <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                                        <option>INV-KOM-012 - PC Desktop Rakitan</option>
                                        <option>INV-KIM-004 - Timbangan Analitik</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tanggal Pemeliharaan</label>
                                    <input type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Update Kondisi Akhir</label>
                                    <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                                        <option value="baik">Baik (Normal)</option>
                                        <option value="kurang_baik">Kurang Baik (Butuh Perbaikan Ringan)</option>
                                        <option value="rusak_berat">Rusak Berat (Tidak Bisa Digunakan)</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Detail Kegiatan Pemeliharaan</label>
                                    <textarea rows="3" placeholder="Jelaskan tindakan yang dilakukan..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </div>

                                <!-- BHP Usage Section -->
                                <div class="md:col-span-2 bg-gray-50 p-4 border border-gray-200 rounded-md" x-data="{ pakaiBhp: false }">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" x-model="pakaiBhp" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label class="font-medium text-gray-700">Pemeliharaan ini menggunakan Barang Habis Pakai (BHP)</label>
                                        </div>
                                    </div>
                                    
                                    <div x-show="pakaiBhp" class="mt-4 pl-7" style="display: none;">
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-medium text-gray-500 uppercase">Pilih BHP</label>
                                                <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                                                    <option>Thermal Paste (Sisa: 5 Tube)</option>
                                                    <option>Cairan Pembersih Kontak (Sisa: 2 Botol)</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase">Jumlah Terpakai</label>
                                                <input type="number" value="1" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">Stok BHP yang dipilih akan otomatis terpotong saat form ini disimpan.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <button type="button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Batal</button>
                                <button type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
