<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-slate-800 tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                {{ __('Manajemen Kepala Laboratorium') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen relative overflow-hidden" x-data="{ tab: '{{ request('tab', 'ringkasan') }}', showAddModal: false, activeEditModal: null }">
        <!-- Background Elements -->
        <div class="absolute top-0 right-0 w-1/3 h-96 bg-gradient-to-bl from-indigo-200/40 via-purple-200/20 to-transparent blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute bottom-0 left-0 w-1/2 h-96 bg-gradient-to-tr from-blue-200/30 to-transparent blur-3xl pointer-events-none -z-10"></div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Alerts --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="bg-emerald-500/10 backdrop-blur-md border border-emerald-500/20 text-emerald-700 px-6 py-4 rounded-2xl flex items-center justify-between" x-transition>
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-500 rounded-full p-1"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" class="bg-rose-500/10 backdrop-blur-md border border-rose-500/20 text-rose-700 px-6 py-4 rounded-2xl flex items-center justify-between" x-transition>
                    <div class="flex items-center gap-3">
                        <div class="bg-rose-500 rounded-full p-1"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
            @endif

            <!-- Navigation Tabs -->
            <div class="bg-white/60 backdrop-blur-xl border border-white/80 p-1.5 rounded-2xl shadow-sm inline-flex flex-wrap gap-1">
                <button @click="tab = 'ringkasan'" :class="tab === 'ringkasan' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">
                    Ringkasan
                </button>
                <button @click="tab = 'inventaris'" :class="tab === 'inventaris' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">
                    Kelola Inventaris
                </button>
                <button @click="tab = 'bhp'" :class="tab === 'bhp' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">
                    Kelola Pengajuan BHP
                </button>
                <button @click="tab = 'ruangan'" :class="tab === 'ruangan' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">
                    Distribusi Ruangan
                </button>
            </div>

            <!-- TAB 1: RINGKASAN -->
            <div x-show="tab === 'ringkasan'" class="space-y-8" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Stats Cards -->
                    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-white shadow-xl shadow-slate-200/30">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500">Total Aset Aktif</p>
                        <h4 class="text-3xl font-bold text-slate-800 mt-1">{{ $totalInventory }}</h4>
                    </div>

                    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-white shadow-xl shadow-slate-200/30">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500">Estimasi Nilai Aset</p>
                        <h4 class="text-2xl font-bold text-slate-800 mt-1">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</h4>
                    </div>

                    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-white shadow-xl shadow-slate-200/30">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500">Menunggu Review</p>
                        <h4 class="text-3xl font-bold text-slate-800 mt-1">{{ $pendingApproval }}</h4>
                    </div>

                    <div class="bg-slate-900 rounded-3xl p-6 shadow-xl relative overflow-hidden">
                        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(ellipse_at_bottom_right,_var(--tw-gradient-stops))] from-indigo-500 to-transparent"></div>
                        <div class="relative z-10">
                            <div class="w-12 h-12 rounded-2xl bg-white/10 text-white flex items-center justify-center mb-4 border border-white/20">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <p class="text-sm font-medium text-indigo-200">Data Terkunci (Locked)</p>
                            <h4 class="text-3xl font-bold text-white mt-1">{{ $lockedCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: INVENTARIS -->
            <div x-show="tab === 'inventaris'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex justify-between items-center bg-white/80 backdrop-blur-xl p-4 rounded-2xl shadow-sm border border-slate-200/60">
                    <h3 class="text-lg font-bold text-slate-800 px-2">Data Inventaris</h3>
                    <button @click="showAddModal = true; categoryType = 'inventaris'" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium text-sm shadow-lg shadow-indigo-200 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Tambah Inventaris Baru
                    </button>
                </div>

                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                                <th class="px-6 py-4">Nama Barang</th>
                                <th class="px-6 py-4">Status Approval</th>
                                <th class="px-6 py-4">Kondisi</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($inventarisItems as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-800">{{ $item->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->label_code ?? 'Auto Generate' }} &bull; {{ $item->room->name ?? 'Belum dialokasikan' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->approval_status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-700">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            Locked (Disetujui)
                                        </span>
                                    @elseif($item->approval_status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-100 text-rose-700">Ditolak</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-700">Pending Review</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-600">{{ $item->condition_label }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($item->approval_status !== 'approved')
                                        <button @click="activeEditModal = {{ $item->id }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">Edit</button>
                                    @else
                                        <button disabled class="text-slate-400 bg-slate-50 px-3 py-1.5 rounded-lg text-sm font-medium cursor-not-allowed" title="Terkunci oleh Kaprodi">Locked</button>
                                    @endif
                                </td>
                            </tr>

                            <!-- Edit Modal for this item -->
                            @if($item->approval_status !== 'approved')
                            <div x-show="activeEditModal === {{ $item->id }}" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div x-show="activeEditModal === {{ $item->id }}" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="activeEditModal = null"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    
                                    <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                                        <form action="{{ route('kepala_lab.inventory.update', $item) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="category" value="{{ $item->category }}">
                                            
                                            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                                                <h3 class="text-lg font-bold text-slate-800">Edit Data: {{ $item->name }}</h3>
                                            </div>
                                            
                                            <div class="p-6 space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Barang</label>
                                                    <input type="text" name="name" value="{{ $item->name }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-slate-700 mb-1">Merk / Brand</label>
                                                        <input type="text" name="brand" value="{{ $item->brand }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-slate-700 mb-1">Harga Estimasi</label>
                                                        <input type="number" name="price" value="{{ $item->price }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-1">Kondisi</label>
                                                    <select name="condition" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                                        <option value="baik" {{ $item->condition == 'baik' ? 'selected' : '' }}>Baik</option>
                                                        <option value="kurang_baik" {{ $item->condition == 'kurang_baik' ? 'selected' : '' }}>Kurang Baik</option>
                                                        <option value="rusak_ringan" {{ $item->condition == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                                        <option value="rusak_berat" {{ $item->condition == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-1">Ruangan</label>
                                                    <select name="room_id" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                                        <option value="">-- Pilih Ruangan --</option>
                                                        @foreach($rooms as $r)
                                                            <option value="{{ $r->id }}" {{ $item->room_id == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 rounded-b-3xl">
                                                <button type="button" @click="activeEditModal = null" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors font-medium text-sm">Batal</button>
                                                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors font-medium text-sm">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: BHP -->
            <div x-show="tab === 'bhp'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex justify-between items-center bg-white/80 backdrop-blur-xl p-4 rounded-2xl shadow-sm border border-slate-200/60">
                    <h3 class="text-lg font-bold text-slate-800 px-2">Data BHP (Barang Habis Pakai)</h3>
                    <button @click="showAddModal = true; categoryType = 'bhp'" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium text-sm shadow-lg shadow-indigo-200 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Pengajuan BHP Baru
                    </button>
                </div>

                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                                <th class="px-6 py-4">Nama BHP</th>
                                <th class="px-6 py-4">Status Approval</th>
                                <th class="px-6 py-4">Ruangan</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($bhpCategoryItems as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-800">{{ $item->name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->approval_status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-700">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            Locked (Disetujui)
                                        </span>
                                    @elseif($item->approval_status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-100 text-rose-700">Ditolak</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-700">Pending Review</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $item->room->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($item->approval_status !== 'approved')
                                        <button @click="activeEditModal = {{ $item->id }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">Edit</button>
                                    @else
                                        <button disabled class="text-slate-400 bg-slate-50 px-3 py-1.5 rounded-lg text-sm font-medium cursor-not-allowed" title="Terkunci">Locked</button>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Edit Modal (sama logic-nya dengan atas) -->
                            @if($item->approval_status !== 'approved')
                            <div x-show="activeEditModal === {{ $item->id }}" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="activeEditModal = null"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                    <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                                        <form action="{{ route('kepala_lab.inventory.update', $item) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="category" value="{{ $item->category }}">
                                            <input type="hidden" name="condition" value="baik">
                                            
                                            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200"><h3 class="text-lg font-bold text-slate-800">Edit Pengajuan BHP: {{ $item->name }}</h3></div>
                                            <div class="p-6 space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama BHP</label>
                                                    <input type="text" name="name" value="{{ $item->name }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-1">Harga Estimasi</label>
                                                    <input type="number" name="price" value="{{ $item->price }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-1">Alokasi Ruangan</label>
                                                    <select name="room_id" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                                        <option value="">-- Pilih Ruangan --</option>
                                                        @foreach($rooms as $r)
                                                            <option value="{{ $r->id }}" {{ $item->room_id == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 rounded-b-3xl">
                                                <button type="button" @click="activeEditModal = null" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors font-medium text-sm">Batal</button>
                                                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors font-medium text-sm">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: RUANGAN -->
            <div x-show="tab === 'ruangan'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($rooms as $room)
                    <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-lg shadow-slate-200/30 hover:-translate-y-1 transition-transform">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h4 class="text-xl font-bold text-slate-800">{{ $room->name }}</h4>
                                <span class="text-sm font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md mt-1 inline-block">{{ $room->code }}</span>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm border-b border-slate-100 pb-2">
                                <span class="text-slate-500">Total Item</span>
                                <span class="font-bold text-slate-800">{{ $room->total_items }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm border-b border-slate-100 pb-2">
                                <span class="text-slate-500">Kondisi Baik</span>
                                <span class="font-bold text-emerald-600">{{ $room->items_baik }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500">Kondisi Rusak</span>
                                <span class="font-bold text-rose-600">{{ $room->items_rusak }}</span>
                            </div>
                        </div>

                        <!-- Itemized Breakdown (Auto-Count) -->
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Detail Barang (Auto-Count):</p>
                            @if($room->item_breakdown->count() > 0)
                                <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                                    @foreach($room->item_breakdown as $breakdown)
                                        <div class="flex justify-between items-center text-xs text-slate-600 bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-100">
                                            <span class="font-medium truncate max-w-[180px]">{{ $breakdown->name }}</span>
                                            <span class="bg-indigo-50 text-indigo-700 font-bold px-2 py-0.5 rounded-md">{{ $breakdown->qty }} unit</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-slate-400 italic">Belum ada barang di ruangan ini.</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Global Add Modal -->
            <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="showAddModal = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div x-data="{ categoryType: 'inventaris' }" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                        <form action="{{ route('kepala_lab.inventory.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="category" x-model="categoryType">
                            
                            <div class="bg-indigo-600 px-6 py-6 border-b border-indigo-700 relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                                <h3 class="text-xl font-bold text-white relative z-10" x-text="categoryType === 'inventaris' ? 'Tambah Inventaris Baru' : 'Pengajuan BHP Baru'"></h3>
                                <p class="text-indigo-200 text-sm mt-1 relative z-10">Data akan masuk status Pending untuk direview Kaprodi.</p>
                            </div>
                            
                            <div class="p-6 space-y-4">
                                <!-- Tab Switcher in Modal -->
                                <div class="flex gap-2 p-1 bg-slate-100 rounded-xl mb-4">
                                    <button type="button" @click="categoryType = 'inventaris'" :class="categoryType === 'inventaris' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-1.5 text-sm font-medium rounded-lg transition-all">Inventaris</button>
                                    <button type="button" @click="categoryType = 'bhp'" :class="categoryType === 'bhp' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-1.5 text-sm font-medium rounded-lg transition-all">BHP</button>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                                    <input type="text" name="name" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div x-show="categoryType === 'inventaris'">
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Merk / Brand</label>
                                        <input type="text" name="brand" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                    </div>
                                    <div :class="categoryType === 'inventaris' ? '' : 'col-span-2'">
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Harga Estimasi (Rp)</label>
                                        <input type="number" name="price" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Alokasi Ruangan</label>
                                    <select name="room_id" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                        <option value="">-- Pilih Ruangan --</option>
                                        @foreach($rooms as $r)
                                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi Tambahan</label>
                                    <textarea name="description" rows="3" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200"></textarea>
                                </div>
                            </div>
                            
                            <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 rounded-b-3xl">
                                <button type="button" @click="showAddModal = false" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors font-medium text-sm">Batal</button>
                                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors font-medium text-sm shadow-lg shadow-indigo-200">Simpan & Ajukan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
