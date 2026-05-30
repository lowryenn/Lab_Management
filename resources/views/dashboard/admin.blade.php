<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Administrator Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Welcome Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">Selamat Datang, {{ Auth::user()->name }}</h3>
                        <p class="mt-1 text-sm text-gray-500">Anda berada di panel Administrator sistem manajemen laboratorium.</p>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Stat Card 1 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center">
                    <div class="p-3 rounded-full bg-indigo-50 text-indigo-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Pengguna</p>
                        <h4 class="text-3xl font-bold text-gray-900">124</h4>
                    </div>
                </div>

                <!-- Stat Card 2 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center">
                    <div class="p-3 rounded-full bg-emerald-50 text-emerald-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Laboratorium Aktif</p>
                        <h4 class="text-3xl font-bold text-gray-900">8</h4>
                    </div>
                </div>

                <!-- Stat Card 3 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center">
                    <div class="p-3 rounded-full bg-rose-50 text-rose-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Log Sistem Hari Ini</p>
                        <h4 class="text-3xl font-bold text-gray-900">1,024</h4>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden lg:col-span-1">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h4 class="text-base font-semibold text-gray-900">Akses Cepat</h4>
                    </div>
                    <div class="p-4 space-y-2">
                        <a href="{{ route('users.index') }}" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-50 hover:text-indigo-600 transition">
                            <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Kelola Pengguna
                        </a>
                        <a href="{{ route('rooms.index') }}" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-50 hover:text-indigo-600 transition">
                            <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            Master Data Lab
                        </a>
                        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-50 hover:text-indigo-600 transition">
                            <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Pengaturan Sistem
                        </a>
                    </div>
                </div>

                <!-- Recent Logs -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden lg:col-span-2">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h4 class="text-base font-semibold text-gray-900">Aktivitas Sistem Terkini</h4>
                        <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Lihat Semua</a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Hari ini, 14:30</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Budi (Kepala Lab)</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Menyetujui Pengadaan PC</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sukses</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Hari ini, 11:15</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Siti (Staf Lab)</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Update Stok BHP Kertas</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sukses</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Kemarin, 09:00</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Admin System</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Backup Database Mingguan</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sukses</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Kemarin, 08:45</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Andi (Kaprodi)</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Gagal Login (Password Salah)</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Gagal</span>
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
