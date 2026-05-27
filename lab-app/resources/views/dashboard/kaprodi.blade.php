<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Ketua Program Studi') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen" x-data="{ tab: 'ringkasan' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Navigation Tabs (Clean UI) -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="tab = 'ringkasan'" :class="tab === 'ringkasan' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Ringkasan Eksekutif</button>
                    <button @click="tab = 'review_draf'" :class="tab === 'review_draf' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Review Draf Pengadaan</button>
                    <button @click="tab = 'finalisasi'" :class="tab === 'finalisasi' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Finalisasi Draf</button>
                </nav>
            </div>

            <!-- TAB 1: RINGKASAN -->
            <div x-show="tab === 'ringkasan'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                
                <!-- Welcome Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-6">
                    <h3 class="text-2xl font-bold text-gray-900">Selamat Datang, {{ Auth::user()->name }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Ringkasan eksekutif dan persetujuan pengadaan strategis Program Studi Anda.</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Total Nilai Aset Prodi</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">Rp 1.2M</h4>
                        <p class="mt-2 text-sm text-gray-500">Dari 8 Laboratorium</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Serapan Anggaran</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">68%</h4>
                        <p class="mt-2 text-sm text-gray-500">Semester Ganjil 2026</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Pengajuan Skala Besar</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">2</h4>
                        <p class="mt-2 text-sm text-amber-600 font-medium">Menunggu Persetujuan Final</p>
                    </div>
                </div>
            </div>

            <!-- TAB 2: REVIEW DRAF -->
            <div x-show="tab === 'review_draf'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Review Item Pengadaan</h3>
                            <p class="mt-1 text-sm text-gray-500">Pilih item yang akan disetujui atau ditolak dari pengajuan Kepala Lab.</p>
                        </div>
                        <select class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option>DRF-2026-002 (Lab Komputer)</option>
                            <option>DRF-2026-003 (Lab Elektronika)</option>
                        </select>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimasi Satuan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status Item</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Keputusan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">Monitor 24" IPS Dell</div>
                                        <div class="text-sm text-indigo-600 hover:text-indigo-900"><a href="#">Lihat Katalog</a></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">5</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp 2.500.000</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rp 12.500.000</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-medium rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                            Menunggu Review
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <button type="button" class="text-emerald-600 hover:text-emerald-900 mx-2 font-semibold">Setujui</button>
                                        <button type="button" class="text-rose-600 hover:text-rose-900 mx-2 font-semibold">Tolak</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">Kabel HDMI 5m V2.1</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">10</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp 75.000</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rp 750.000</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-medium rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Disetujui
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <button type="button" class="text-gray-400 cursor-not-allowed mx-2" disabled>Setujui</button>
                                        <button type="button" class="text-rose-600 hover:text-rose-900 mx-2 font-semibold">Batalkan</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 3: FINALISASI DRAF -->
            <div x-show="tab === 'finalisasi'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center max-w-3xl mx-auto">
                    
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 mb-4">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    
                    <h3 class="text-xl font-medium text-gray-900">Finalisasi Draf Pengadaan</h3>
                    <p class="mt-2 text-sm text-gray-500">Mengunci draf akan meneruskannya secara otomatis ke Staf Administrasi. Data tidak akan bisa diubah lagi oleh siapapun setelah ini.</p>
                    
                    <div class="mt-6 text-left bg-gray-50 p-6 rounded-md border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Draf untuk Difinalisasi</label>
                        <select class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                            <option>DRF-2026-002 - Lab Komputer (Total Disetujui: Rp 13.250.000)</option>
                        </select>
                        
                        <div class="mt-4 flex bg-yellow-50 p-4 border border-yellow-200 rounded-md">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Peringatan</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Ada 1 item dalam draf ini yang statusnya ditolak. Item tersebut tidak akan diteruskan ke Staf Administrasi.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="button" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Finalisasi & Kunci Draf Permanen
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
