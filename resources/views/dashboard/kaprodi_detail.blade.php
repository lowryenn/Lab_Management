<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-slate-800 tracking-tight flex items-center gap-2">
                <a href="{{ route('kaprodi.dashboard', ['tab' => 'review']) }}" class="text-slate-400 hover:text-indigo-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                Detail Pengajuan Inventaris
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen relative overflow-hidden" x-data="{ showRejectModal: false }">
        <!-- Background accents -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl shadow-slate-200/50 border border-white overflow-hidden">
                
                <!-- Header Card -->
                <div class="bg-slate-900 p-8 relative overflow-hidden">
                    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-300 via-transparent to-transparent"></div>
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center text-white shadow-lg">
                                @if($item->category === 'inventaris')
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                @else
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-white">{{ $item->name }}</h3>
                                <div class="flex items-center gap-3 mt-2 text-indigo-200 text-sm">
                                    <span class="bg-indigo-500/30 px-3 py-1 rounded-lg border border-indigo-500/50">{{ ucfirst($item->category) }}</span>
                                    <span>{{ $item->brand ?? 'Tanpa Merk' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-indigo-200 text-sm font-medium">Estimasi Harga</p>
                            <p class="text-3xl font-bold text-white mt-1">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Content Grid -->
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- Left Column: Details -->
                    <div class="space-y-6">
                        <h4 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-2">Informasi Barang</h4>
                        
                        <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
                            <div>
                                <p class="text-slate-500 font-medium mb-1">Kode Label Request</p>
                                <p class="text-slate-800 font-semibold bg-slate-100 px-3 py-1.5 rounded-lg inline-block">{{ $item->label_code ?? 'Auto Generate' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 font-medium mb-1">Kondisi Awal</p>
                                <p class="text-slate-800 font-semibold">{{ $item->condition_label }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 font-medium mb-1">Alokasi Ruangan</p>
                                <p class="text-slate-800 font-semibold">{{ $item->room->name ?? 'Belum ditentukan' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 font-medium mb-1">Tanggal Pembelian</p>
                                <p class="text-slate-800 font-semibold">{{ $item->purchase_date ? $item->purchase_date->format('d M Y') : '-' }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-slate-500 font-medium mb-1">Deskripsi Tambahan</p>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-slate-700">
                                    {{ $item->description ?? 'Tidak ada deskripsi.' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Approval Action -->
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 flex flex-col justify-between">
                        <div>
                            <h4 class="text-lg font-bold text-slate-800 mb-2">Tindakan Kaprodi</h4>
                            <p class="text-sm text-slate-600 mb-6">Sebagai Kaprodi, Anda harus meninjau pengajuan ini. Jika disetujui, data akan <strong class="text-indigo-600">dikunci (Locked)</strong> dan diteruskan ke Staff Admin untuk proses Purchase Order atau pelabelan QR.</p>
                            
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3 mb-6">
                                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-xs text-amber-700">Pastikan anggaran sesuai. Aksi persetujuan tidak dapat dibatalkan melalui sistem ini tanpa intervensi Super Admin.</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <form action="{{ route('kaprodi.items.approve', $item) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white px-6 py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-500/30 transition-all hover:-translate-y-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Setujui Pengajuan
                                </button>
                            </form>
                            
                            <button @click="showRejectModal = true" type="button" class="flex-1 flex items-center justify-center gap-2 bg-white text-rose-600 border border-rose-200 hover:bg-rose-50 px-6 py-3.5 rounded-xl font-bold shadow-sm transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Tolak
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal Tolak -->
            <div x-show="showRejectModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div x-show="showRejectModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <!-- Modal panel -->
                    <div x-show="showRejectModal" @click.away="showRejectModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                        <form action="{{ route('kaprodi.items.reject', $item) }}" method="POST">
                            @csrf
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-8 sm:pb-6">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-bold text-slate-900" id="modal-title">
                                            Tolak Pengajuan Inventaris
                                        </h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-slate-500 mb-4">Berikan alasan mengapa pengajuan barang <strong>{{ $item->name }}</strong> ini ditolak. Alasan ini akan dibaca oleh Kepala Lab.</p>
                                            
                                            <textarea name="rejection_reason" rows="4" class="w-full border-slate-200 rounded-xl shadow-sm focus:border-rose-500 focus:ring focus:ring-rose-200 focus:ring-opacity-50 text-sm" placeholder="Contoh: Anggaran semester ini sudah habis..." required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-4 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-3">
                                <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-rose-600 text-base font-medium text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                    Konfirmasi Tolak
                                </button>
                                <button @click="showRejectModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-6 py-2.5 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
