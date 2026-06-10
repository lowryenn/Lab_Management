<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-slate-800 tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ __('Review & Approval Kaprodi') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen relative overflow-hidden" x-data="{ tab: '{{ request('tab', 'ringkasan') }}' }">
        <!-- Decorative Background Elements -->
        <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-br from-indigo-600/10 via-purple-500/5 to-transparent pointer-events-none -z-10"></div>
        <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-bl from-blue-400/10 to-transparent blur-3xl pointer-events-none -z-10"></div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="bg-emerald-500/10 backdrop-blur-md border border-emerald-500/20 text-emerald-700 px-6 py-4 rounded-2xl relative shadow-lg shadow-emerald-500/5 flex items-center justify-between transition-all duration-500" x-transition:leave="opacity-0 translate-y-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-500 rounded-full p-1"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
            @endif

            <!-- Glassmorphic Tabs -->
            <div class="bg-white/60 backdrop-blur-xl border border-white/80 p-1.5 rounded-2xl shadow-sm inline-flex space-x-1">
                <button @click="tab = 'ringkasan'" :class="tab === 'ringkasan' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300 relative overflow-hidden group">
                    <span class="relative z-10 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Ringkasan
                    </span>
                    <div class="absolute inset-0 bg-indigo-50/50 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </button>
                <button @click="tab = 'review'" :class="tab === 'review' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300 relative overflow-hidden group">
                    <span class="relative z-10 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        Review Pengajuan 
                        @if($pendingCount > 0)
                            <span class="ml-1 bg-rose-500 text-white text-[10px] px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                        @endif
                    </span>
                    <div class="absolute inset-0 bg-indigo-50/50 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </button>
                <button @click="tab = 'riwayat'" :class="tab === 'riwayat' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300 relative overflow-hidden group">
                    <span class="relative z-10 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Riwayat Keputusan
                    </span>
                    <div class="absolute inset-0 bg-indigo-50/50 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </button>
            </div>

            <!-- TAB 1: RINGKASAN -->
            <div x-show="tab === 'ringkasan'" class="space-y-8" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                
                <!-- Welcome Banner -->
                <div class="relative overflow-hidden bg-slate-900 rounded-3xl p-8 lg:p-10 shadow-2xl">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
                    <div class="absolute top-0 right-48 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
                    
                    <div class="relative z-10">
                        <h3 class="text-3xl lg:text-4xl font-bold text-white tracking-tight">Selamat Datang, {{ Auth::user()->name }}</h3>
                        <p class="mt-3 text-lg text-indigo-200 max-w-2xl font-light">Pusat komando persetujuan inventaris dan pengadaan Program Studi. Semua keputusan pengadaan akan dikunci setelah disetujui.</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1 transition-transform duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Nilai Aset Disetujui</p>
                                <h4 class="mt-2 text-2xl font-bold text-slate-800">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</h4>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm text-indigo-600 font-medium bg-indigo-50 inline-flex px-3 py-1 rounded-full">
                            Terkunci & Aman
                        </div>
                    </div>

                    <div class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1 transition-transform duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Menunggu Review</p>
                                <h4 class="mt-2 text-3xl font-bold text-slate-800">{{ $pendingCount }}</h4>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-slate-500">Item butuh keputusan Anda</div>
                    </div>

                    <div class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1 transition-transform duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Disetujui</p>
                                <h4 class="mt-2 text-3xl font-bold text-slate-800">{{ $approvedCount }}</h4>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-slate-500">Diteruskan ke Staff Admin</div>
                    </div>

                    <div class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1 transition-transform duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Ditolak</p>
                                <h4 class="mt-2 text-3xl font-bold text-slate-800">{{ $rejectedCount }}</h4>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-400 to-red-500 flex items-center justify-center text-white shadow-lg shadow-rose-500/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-slate-500">Pengajuan dibatalkan</div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: REVIEW PENGAJUAN -->
            <div x-show="tab === 'review'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-200/60 bg-white/50 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">Menunggu Review Anda</h3>
                            <p class="mt-1 text-sm text-slate-500">Data ini diinput oleh Kepala Lab dan belum aktif hingga Anda menyetujuinya.</p>
                        </div>
                    </div>
                    
                    @if($pendingItems->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                                        <th class="px-8 py-4">Item & Detail</th>
                                        <th class="px-8 py-4">Kategori / Ruang</th>
                                        <th class="px-8 py-4">Estimasi Harga</th>
                                        <th class="px-8 py-4 text-center">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($pendingItems as $item)
                                    <tr class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center flex-shrink-0 text-indigo-500">
                                                    @if($item->category === 'inventaris')
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                                    @else
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h4 class="text-base font-bold text-slate-800">{{ $item->name }}</h4>
                                                    <div class="flex items-center gap-2 mt-1 text-sm text-slate-500">
                                                        <span>{{ $item->brand ?? 'Tanpa Merk' }}</span>
                                                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                        <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium">{{ $item->condition_label }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex flex-col">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->category === 'inventaris' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }} w-max mb-1">
                                                    {{ ucfirst($item->category) }}
                                                </span>
                                                <span class="text-sm text-slate-600">{{ $item->room->name ?? 'Belum dialokasikan' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <span class="text-sm font-bold text-slate-800 bg-slate-100 px-3 py-1 rounded-lg">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="px-8 py-5 text-center">
                                            <a href="{{ route('kaprodi.items.show', $item) }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-900 text-white rounded-xl text-sm font-medium hover:bg-indigo-600 transition-colors shadow-lg shadow-slate-900/20 group-hover:shadow-indigo-500/25">
                                                Lihat Detail & Eksekusi
                                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-16 text-center">
                            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700">Tidak ada pengajuan pending</h3>
                            <p class="text-slate-500 mt-1">Semua data inventaris baru telah Anda review.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 3: RIWAYAT KEPUTUSAN -->
            <div x-show="tab === 'riwayat'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-200/60 bg-white/50">
                        <h3 class="text-xl font-bold text-slate-800">Riwayat Keputusan Kaprodi</h3>
                        <p class="mt-1 text-sm text-slate-500">Daftar item yang sudah Anda setujui atau tolak (Locked).</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                                    <th class="px-8 py-4">Item</th>
                                    <th class="px-8 py-4 text-center">Status</th>
                                    <th class="px-8 py-4">Waktu Keputusan</th>
                                    <th class="px-8 py-4">Catatan / Alasan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($reviewedItems as $item)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-4">
                                        <p class="font-bold text-slate-800">{{ $item->name }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                    </td>
                                    <td class="px-8 py-4 text-center">
                                        @if($item->approval_status === 'approved')
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 shadow-sm shadow-emerald-500/10">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Disetujui
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200 shadow-sm shadow-rose-500/10">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                Ditolak
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-4 text-sm text-slate-600">
                                        {{ $item->approved_at ? $item->approved_at->format('d M Y H:i') : '-' }}
                                    </td>
                                    <td class="px-8 py-4 text-sm text-slate-600 italic">
                                        {{ $item->rejection_reason ?? '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center text-slate-500">
                                        Belum ada riwayat keputusan.
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
