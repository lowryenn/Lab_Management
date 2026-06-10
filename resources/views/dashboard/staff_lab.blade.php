<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-slate-800 tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                {{ __('Operasional Laboratorium') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen relative overflow-hidden" x-data="{ tab: '{{ request('tab', 'ringkasan') }}', showBulkModal: false, showConditionModal: false, showMaintenanceModal: false, activeItem: null }">
        <div class="absolute top-0 right-0 w-1/3 h-full bg-gradient-to-bl from-teal-100/30 via-emerald-100/10 to-transparent blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute bottom-0 left-0 w-1/2 h-96 bg-gradient-to-tr from-indigo-100/30 to-transparent blur-3xl pointer-events-none -z-10"></div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            {{-- Alerts --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="bg-emerald-500/10 backdrop-blur-md border border-emerald-500/20 text-emerald-700 px-6 py-4 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-500 rounded-full p-1"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
            @endif

            @if($errors->any())
                <div x-data="{ show: true }" x-show="show" class="bg-rose-500/10 backdrop-blur-md border border-rose-500/20 text-rose-700 px-6 py-4 rounded-2xl flex items-start justify-between shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="bg-rose-500 rounded-full p-1 mt-0.5"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>
                        <div class="font-medium">
                            <p class="mb-1">Terjadi kesalahan:</p>
                            <ul class="list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700 transition mt-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
            @endif

            <!-- Glassmorphic Tabs -->
            <div class="bg-white/60 backdrop-blur-xl border border-white/80 p-1.5 rounded-2xl shadow-sm inline-flex flex-wrap gap-1">
                <button @click="tab = 'ringkasan'" :class="tab === 'ringkasan' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">Ringkasan</button>
                <button @click="tab = 'bulk_bhp'" :class="tab === 'bulk_bhp' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">
                    Pemakaian BHP (Bulk)
                </button>
                <button @click="tab = 'kelola_stok'" :class="tab === 'kelola_stok' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">
                    Kelola Stok BHP @if($bhpLow > 0)<span class="ml-1 bg-rose-500 text-white text-[10px] px-2 py-0.5 rounded-full">{{ $bhpLow }}</span>@endif
                </button>
                <button @click="tab = 'kondisi_barang'" :class="tab === 'kondisi_barang' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">
                    Kondisi & Maintenance
                </button>
            </div>

            <!-- TAB 1: RINGKASAN -->
            <div x-show="tab === 'ringkasan'" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl p-6 shadow-xl shadow-indigo-500/20 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                        <div class="relative z-10 flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100 font-medium">Total Aset Laboratorium</p>
                                <h4 class="text-4xl font-bold mt-2">{{ $totalAssets }}</h4>
                            </div>
                            <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center border border-white/20">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-lg shadow-slate-200/30 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Peringatan Stok BHP</p>
                            <h4 class="text-3xl font-bold mt-1 {{ $bhpLow > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $bhpLow }} Item</h4>
                            @if($bhpLow > 0)
                                <p class="text-xs text-rose-500 mt-1 font-medium flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Stok Menipis</p>
                            @else
                                <p class="text-xs text-emerald-500 mt-1 font-medium">Semua stok aman</p>
                            @endif
                        </div>
                        <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-lg shadow-slate-200/30 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Maintenance Minggu Ini</p>
                            <h4 class="text-3xl font-bold mt-1 text-slate-800">{{ $maintenanceDue }}</h4>
                        </div>
                        <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <!-- Recent BHP Usage -->
                    <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-lg shadow-slate-200/30">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">Aktivitas Pemakaian BHP Terbaru</h3>
                        <div class="space-y-4 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                            @forelse($recentTransactions as $tx)
                                <div class="flex items-center gap-4 p-3 rounded-2xl {{ $tx->type === 'out' ? 'bg-rose-50/50 border border-rose-100/50' : 'bg-emerald-50/50 border border-emerald-100/50' }}">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $tx->type === 'out' ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600' }}">
                                        @if($tx->type === 'out')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4m8-8l-8 8 8 8"></path></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-slate-800 text-sm">{{ $tx->bhpItem->name ?? 'BHP Dihapus' }}</p>
                                        <p class="text-xs text-slate-500">{{ $tx->user->name ?? 'System' }} &bull; {{ $tx->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold {{ $tx->type === 'out' ? 'text-rose-600' : 'text-emerald-600' }}">
                                            {{ $tx->type === 'out' ? '-' : '+' }}{{ $tx->quantity }}
                                        </p>
                                        <p class="text-xs text-slate-400">Sisa: {{ $tx->stock_after }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-sm text-slate-500 py-4">Belum ada riwayat transaksi BHP.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Recent Condition Changes -->
                    <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-lg shadow-slate-200/30">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">Log Pembaruan Kondisi Barang</h3>
                        <div class="space-y-4 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                            @forelse($conditionLogs as $log)
                                <div class="flex items-start gap-4 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                    <div class="w-10 h-10 rounded-xl bg-slate-200 text-slate-600 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm">{{ $log->inventoryItem->name ?? 'Barang Dihapus' }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs px-2 py-0.5 bg-slate-200 text-slate-700 rounded">{{ $log->condition_before }}</span>
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                            <span class="text-xs px-2 py-0.5 rounded {{ $log->condition_after === 'rusak_berat' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $log->condition_after }}</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">{{ $log->user->name ?? 'System' }} &bull; {{ $log->created_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-sm text-slate-500 py-4">Belum ada riwayat pembaruan kondisi.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: BULK BHP -->
            <div x-show="tab === 'bulk_bhp'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-8 shadow-xl shadow-slate-200/50">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800">Pemakaian BHP (Bulk Input)</h3>
                            <p class="text-slate-500 mt-1">Gunakan form ini untuk mencatat pemakaian banyak BHP sekaligus dalam satu kegiatan praktikum/lab.</p>
                        </div>
                    </div>

                    <form action="{{ route('staff_lab.bhp.bulk') }}" method="POST" x-data="{ 
                        rows: [{ id: 1, bhp_item_id: '', quantity: 1, description: '' }],
                        addRow() { this.rows.push({ id: Date.now(), bhp_item_id: '', quantity: 1, description: '' }) },
                        removeRow(id) { if(this.rows.length > 1) this.rows = this.rows.filter(r => r.id !== id) }
                    }">
                        @csrf
                        <div class="space-y-4 mb-6">
                            <template x-for="(row, index) in rows" :key="row.id">
                                <div class="flex flex-col md:flex-row gap-4 p-4 bg-slate-50 border border-slate-200 rounded-2xl items-start md:items-end relative group transition-all hover:border-indigo-300">
                                    <div class="w-full md:w-2/5">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Item BHP</label>
                                        <select :name="'items['+index+'][bhp_item_id]'" x-model="row.bhp_item_id" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm" required>
                                            <option value="">-- Pilih Barang --</option>
                                            @foreach($bhpItems as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }} (Sisa: {{ $b->stock }} {{ $b->unit }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-full md:w-1/5">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jumlah</label>
                                        <input type="number" :name="'items['+index+'][quantity]'" x-model="row.quantity" min="1" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm" required>
                                    </div>
                                    <div class="w-full md:w-2/5">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Keterangan (Opsional)</label>
                                        <input type="text" :name="'items['+index+'][description]'" x-model="row.description" placeholder="Mata kuliah / Kegiatan" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                                    </div>
                                    <button type="button" @click="removeRow(row.id)" x-show="rows.length > 1" class="absolute -top-3 -right-3 w-8 h-8 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center hover:bg-rose-500 hover:text-white shadow-sm transition-colors opacity-0 group-hover:opacity-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        
                        <div class="flex justify-between items-center border-t border-slate-200 pt-6">
                            <button type="button" @click="addRow()" class="flex items-center gap-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-4 py-2.5 rounded-xl font-medium text-sm transition-colors border border-indigo-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Tambah Baris Baru
                            </button>
                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-slate-900/20 transition-all flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Proses Pemakaian Bulk
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 3: KELOLA STOK BHP -->
            <div x-show="tab === 'kelola_stok'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-xl shadow-slate-200/50">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-slate-800">Daftar Stok BHP</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                                    <th class="px-6 py-4">Nama Item</th>
                                    <th class="px-6 py-4">Sisa Stok</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Update Manual</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($bhpItems as $bhp)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-800">{{ $bhp->name }}</p>
                                        <p class="text-xs text-slate-500">Min: {{ $bhp->min_stock }} {{ $bhp->unit }} &bull; {{ $bhp->room->name ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-lg font-bold {{ $bhp->stock <= $bhp->min_stock ? 'text-rose-600' : 'text-slate-800' }}">{{ $bhp->stock }}</span>
                                        <span class="text-sm text-slate-500 ml-1">{{ $bhp->unit }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($bhp->stock <= $bhp->min_stock)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-100 text-rose-700 animate-pulse">Kritis</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-700">Aman</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('staff_lab.bhp.update-stock', $bhp) }}" method="POST" class="inline-flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" name="stock" value="{{ $bhp->stock }}" class="w-20 rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm py-1.5 px-2" min="0" required>
                                            <button type="submit" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors border border-indigo-100">Set Stok</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 4: KONDISI & MAINTENANCE -->
            <div x-show="tab === 'kondisi_barang'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl shadow-xl shadow-slate-200/50 overflow-hidden">
                    <div class="p-6 border-b border-slate-200/60 bg-white/50">
                        <h3 class="text-xl font-bold text-slate-800">Manajemen Kondisi Inventaris Aktif</h3>
                        <p class="mt-1 text-sm text-slate-500">Jika kondisi diupdate menjadi "Rusak Berat", sistem akan otomatis menandai barang sebagai inaktif dan membuat ID baru untuk barang pengganti.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                                    <th class="px-6 py-4">Barang</th>
                                    <th class="px-6 py-4">Kondisi Saat Ini</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($inventoryItems as $item)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-800">{{ $item->name }}</p>
                                        <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $item->label_code ?? 'ID: '.$item->id }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $badgeClass = match($item->condition) {
                                                'baik' => 'bg-emerald-100 text-emerald-700',
                                                'kurang_baik' => 'bg-amber-100 text-amber-700',
                                                'rusak_ringan' => 'bg-orange-100 text-orange-700',
                                                'rusak_berat' => 'bg-rose-100 text-rose-700',
                                                default => 'bg-slate-100 text-slate-700'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ $badgeClass }}">
                                            {{ $item->condition_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right flex gap-2 justify-end">
                                        <button @click="showConditionModal = true; activeItem = {{ $item->id }}" class="text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors border border-indigo-100">Ubah Kondisi</button>
                                        <button @click="showMaintenanceModal = true; activeItem = {{ $item->id }}" class="text-emerald-600 bg-emerald-50 hover:bg-emerald-600 hover:text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors border border-emerald-100">Catat Maintenance</button>
                                    </td>
                                </tr>

                                <!-- Modal Ubah Kondisi Per Item -->
                                <div x-show="showConditionModal && activeItem === {{ $item->id }}" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showConditionModal = false; activeItem = null"></div>
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                                            <form action="{{ route('staff_lab.inventory.condition', $item) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="px-6 py-6 border-b border-slate-200">
                                                    <h3 class="text-lg font-bold text-slate-800 leading-tight">{{ $item->name }}</h3>
                                                    <p class="text-sm text-slate-500 font-mono mt-1">{{ $item->label_code ?? 'ID: '.$item->id }}</p>
                                                </div>
                                                <div class="p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-slate-700 mb-1">Update Kondisi Baru</label>
                                                        <select name="condition" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
                                                            <option value="baik" {{ $item->condition == 'baik' ? 'selected' : '' }}>Baik</option>
                                                            <option value="kurang_baik" {{ $item->condition == 'kurang_baik' ? 'selected' : '' }}>Kurang Baik</option>
                                                            <option value="rusak_ringan" {{ $item->condition == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                                            <option value="rusak_berat" {{ $item->condition == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat (Akan Di-Replacement)</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                                                        <textarea name="description" rows="3" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200" placeholder="Penyebab kerusakan / Keterangan"></textarea>
                                                    </div>
                                                </div>
                                                <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
                                                    <button type="button" @click="showConditionModal = false; activeItem = null" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-700 bg-white font-medium text-sm">Batal</button>
                                                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 font-medium text-sm shadow-md">Simpan Kondisi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Catat Maintenance Per Item -->
                                <div x-show="showMaintenanceModal && activeItem === {{ $item->id }}" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showMaintenanceModal = false; activeItem = null"></div>
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                                            <form action="{{ route('staff_lab.maintenance.store') }}" method="POST" x-data="{ usesBhp: false }">
                                                @csrf
                                                <input type="hidden" name="inventory_item_id" value="{{ $item->id }}">
                                                <div class="px-6 py-6 border-b border-slate-200">
                                                    <h3 class="text-lg font-bold text-slate-800 leading-tight">Catat Maintenance: {{ $item->name }}</h3>
                                                    <p class="text-sm text-slate-500 font-mono mt-1">{{ $item->label_code ?? 'ID: '.$item->id }}</p>
                                                </div>
                                                <div class="p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Pemeliharaan</label>
                                                        <input type="date" name="maintenance_date" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm" value="{{ date('Y-m-d') }}" required>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-slate-700 mb-1">Kondisi Setelah Maintenance</label>
                                                        <select name="condition_after" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm" required>
                                                            <option value="baik">Baik</option>
                                                            <option value="kurang_baik">Kurang Baik</option>
                                                            <option value="rusak_ringan">Rusak Ringan</option>
                                                            <option value="rusak_berat">Rusak Berat (Akan Di-Replacement)</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-slate-700 mb-1">Catatan Kegiatan / Tindakan</label>
                                                        <textarea name="description" rows="3" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm" placeholder="Tindakan pemeliharaan yang dilakukan..." required></textarea>
                                                    </div>
                                                    
                                                    <!-- BHP Consumption Toggle -->
                                                    <div class="pt-2 border-t border-slate-100">
                                                        <label class="flex items-center gap-2 cursor-pointer">
                                                            <input type="checkbox" name="uses_bhp" value="1" x-model="usesBhp" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                            <span class="text-sm font-medium text-slate-700">Menggunakan BHP (Barang Habis Pakai)</span>
                                                        </label>
                                                    </div>
                                                    
                                                    <div x-show="usesBhp" x-transition class="space-y-4 pt-2">
                                                        <div>
                                                            <label class="block text-sm font-medium text-slate-700 mb-1">Pilih Item BHP</label>
                                                            <select name="bhp_item_id" :required="usesBhp" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                                                                <option value="">-- Pilih BHP --</option>
                                                                @foreach($bhpItems as $b)
                                                                    <option value="{{ $b->id }}">{{ $b->name }} (Sisa: {{ $b->stock }} {{ $b->unit }})</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah Pemakaian BHP</label>
                                                            <input type="number" name="bhp_qty_used" :required="usesBhp" min="1" value="1" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 rounded-b-3xl">
                                                    <button type="button" @click="showMaintenanceModal = false; activeItem = null" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-700 bg-white font-medium text-sm">Batal</button>
                                                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-medium text-sm shadow-md">Simpan Log MT</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
