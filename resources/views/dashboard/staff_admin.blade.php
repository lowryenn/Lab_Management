<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-slate-800 tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                {{ __('Sistem QR & Administrasi') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen relative overflow-hidden" x-data="{ tab: '{{ request('tab', 'ringkasan') }}', showPoModal: false, showReceiptModal: false, activePo: null }">
        <!-- Background Decor -->
        <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-bl from-indigo-100/50 via-purple-100/10 to-transparent blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute bottom-0 left-0 w-1/3 h-96 bg-gradient-to-tr from-sky-200/30 to-transparent blur-3xl pointer-events-none -z-10"></div>

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

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" class="bg-rose-500/10 backdrop-blur-md border border-rose-500/20 text-rose-700 px-6 py-4 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="bg-rose-500 rounded-full p-1"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
            @endif

            <!-- Glassmorphic Tabs -->
            <div class="bg-white/60 backdrop-blur-xl border border-white/80 p-1.5 rounded-2xl shadow-sm inline-flex flex-wrap gap-1">
                <button @click="tab = 'ringkasan'" :class="tab === 'ringkasan' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">Dashboard</button>
                <button @click="tab = 'qr_generate'" :class="tab === 'qr_generate' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">
                    Generate QR @if($pendingQr > 0)<span class="ml-1 bg-amber-500 text-white text-[10px] px-2 py-0.5 rounded-full">{{ $pendingQr }}</span>@endif
                </button>
                <button @click="tab = 'qr_scan'" :class="tab === 'qr_scan' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">Scanner & Logs</button>
                <button @click="tab = 'penerimaan_barang'" :class="tab === 'penerimaan_barang' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">Penerimaan PO</button>
                <button @click="tab = 'update_inventaris'" :class="tab === 'update_inventaris' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">Register Manual</button>
                <button @click="tab = 'inventaris_bhp'" :class="tab === 'inventaris_bhp' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-white/40'" class="px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-300">Inventaris & BHP</button>
            </div>

            <!-- TAB 1: RINGKASAN -->
            <div x-show="tab === 'ringkasan'" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-3xl p-6 shadow-xl shadow-slate-900/20 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
                        <div class="relative z-10 flex items-center justify-between">
                            <div>
                                <p class="text-slate-400 font-medium">Total Inventaris</p>
                                <h4 class="text-4xl font-bold mt-2">{{ $totalItems }}</h4>
                            </div>
                            <div class="w-14 h-14 bg-white/10 backdrop-blur rounded-2xl flex items-center justify-center border border-white/10">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-lg shadow-slate-200/30 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Sudah Dilabeli QR</p>
                            <h4 class="text-3xl font-bold mt-1 text-emerald-600">{{ $itemsWithQr }}</h4>
                        </div>
                        <div class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-lg shadow-slate-200/30 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Belum Dilabeli (Pending)</p>
                            <h4 class="text-3xl font-bold mt-1 {{ $pendingQr > 0 ? 'text-amber-500' : 'text-slate-800' }}">{{ $pendingQr }}</h4>
                        </div>
                        <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: QR GENERATE -->
            <div x-show="tab === 'qr_generate'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl shadow-xl shadow-slate-200/50 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200/60 bg-white/50 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Daftar Inventaris Butuh QR</h3>
                            <p class="text-sm text-slate-500 mt-1">Item yang sudah disetujui Kaprodi namun belum memiliki QR Code.</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                                    <th class="px-6 py-4">Nama Barang</th>
                                    <th class="px-6 py-4">Ruangan</th>
                                    <th class="px-6 py-4 text-right">Aksi Generate</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($noQrItems as $item)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-800">{{ $item->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->label_code ?? 'Auto Generate' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $item->room->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('staff_admin.qr.generate', $item) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-md shadow-indigo-200 transition-colors flex items-center justify-end gap-2 ml-auto">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                                Generate QR
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-slate-500">
                                        Tidak ada item yang menunggu pelabelan QR.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tampilkan Hasil QR Terbaru -->
                @if(session('qr_code'))
                <div class="bg-white rounded-3xl p-8 shadow-xl text-center border border-indigo-100 mt-6 relative overflow-hidden">
                    <div class="absolute inset-0 bg-indigo-50/50 -z-10"></div>
                    <h3 class="text-2xl font-bold text-indigo-900 mb-2">QR Code Berhasil Dibuat</h3>
                    <p class="text-indigo-600 mb-6">Silakan print kode berikut dan tempelkan pada barang.</p>
                    <div class="inline-block p-4 bg-white rounded-2xl shadow-sm border border-slate-100">
                        <!-- Menggunakan base64 SVG data -->
                        <img src="{{ session('qr_internal') }}" alt="QR Code" class="w-48 h-48 mx-auto" id="generated-qr">
                    </div>
                    <p class="font-mono mt-4 text-slate-600 font-bold tracking-widest">{{ session('qr_code') }}</p>
                    <button onclick="window.print()" class="mt-6 bg-slate-900 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg">Print QR Code</button>
                </div>
                @endif

                <!-- Daftar QR Terdaftar & QR Kampus -->
                <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl shadow-xl shadow-slate-200/50 overflow-hidden mt-8">
                    <div class="px-6 py-5 border-b border-slate-200/60 bg-white/50">
                        <h3 class="text-lg font-bold text-slate-800">Daftar Inventaris Terdaftar & QR Kampus</h3>
                        <p class="text-sm text-slate-500 mt-1">Item yang sudah memiliki QR Code internal. Anda dapat memasukkan atau memperbarui kode QR Kampus di sini.</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                                    <th class="px-6 py-4">Nama Barang</th>
                                    <th class="px-6 py-4">QR Internal</th>
                                    <th class="px-6 py-4">QR Kampus</th>
                                    <th class="px-6 py-4 text-right">Update QR Kampus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($qrItems as $item)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-800">{{ $item->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->label_code ?? 'Auto Generate' }} &bull; {{ $item->room->name ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($item->qr_internal)
                                            <img src="{{ $item->qr_internal }}" alt="QR" class="w-10 h-10 border rounded bg-white p-0.5">
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($item->qr_kampus)
                                            <span class="bg-indigo-50/80 text-indigo-700 font-bold px-2.5 py-1.5 rounded-lg text-xs font-mono border border-indigo-100">{{ $item->qr_kampus }}</span>
                                        @else
                                            <span class="text-slate-400 text-xs italic">Belum diinput</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('staff_admin.qr.campus.update', $item) }}" method="POST" class="inline-flex items-center gap-2 justify-end ml-auto">
                                            @csrf
                                            <input type="text" name="qr_kampus" placeholder="Input QR Kampus..." value="{{ $item->qr_kampus }}" class="w-48 rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-xs py-1.5 px-3" required>
                                            <button type="submit" class="bg-slate-900 hover:bg-slate-850 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                                Simpan
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                        Belum ada barang yang memiliki QR Code internal.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 3: SCANNER -->
            <div x-show="tab === 'qr_scan'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Scanner Form -->
                    <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-8 shadow-xl shadow-slate-200/50">
                        <h3 class="text-xl font-bold text-slate-800 mb-2">Pencarian / Scan Manual</h3>
                        <p class="text-sm text-slate-500 mb-6">Masukkan kode QR dari scanner barcode (tekan enter) atau ketik manual.</p>
                        
                        <form action="{{ route('staff_admin.qr.scan') }}" method="POST">
                            @csrf
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-6 w-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                </div>
                                <input type="text" name="qr_code" class="block w-full pl-12 pr-4 py-4 border-slate-300 rounded-2xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-lg font-mono tracking-wider" placeholder="Contoh: LABINV-123-ABCD" autofocus required>
                            </div>
                            <button type="submit" class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-indigo-200 transition-colors">
                                Proses Pencarian
                            </button>
                        </form>
                    </div>

                    <!-- Scan Result -->
                    @if(session('scan_result'))
                        @php $scanned = session('scan_result'); @endphp
                        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl p-8 shadow-xl shadow-indigo-500/20 text-white">
                            <h3 class="text-lg font-bold text-indigo-100 border-b border-indigo-400/50 pb-2 mb-4">Hasil Pencarian Ditemukan</h3>
                            <h2 class="text-3xl font-bold mb-1">{{ $scanned->name }}</h2>
                            <p class="text-indigo-200 font-mono tracking-wider mb-4">{{ $scanned->label_code }}</p>
                            
                            <div class="mt-6 space-y-3">
                                <div class="flex justify-between border-b border-white/10 pb-2">
                                    <span class="text-indigo-200">Kondisi</span>
                                    <span class="font-bold">{{ $scanned->condition_label }}</span>
                                </div>
                                <div class="flex justify-between border-b border-white/10 pb-2">
                                    <span class="text-indigo-200">Ruangan</span>
                                    <span class="font-bold">{{ $scanned->room->name ?? 'Belum ditentukan' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-white/10 pb-2">
                                    <span class="text-indigo-200">QR Kampus</span>
                                    <span class="font-bold font-mono bg-white/20 px-2 py-0.5 rounded">{{ $scanned->qr_kampus ?? 'Belum Diinput' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-white/10 pb-2">
                                    <span class="text-indigo-200">Kategori</span>
                                    <span class="font-bold">{{ ucfirst($scanned->category) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-indigo-200">Status</span>
                                    <span class="font-bold">{{ $scanned->status === 'active' ? 'Aktif' : 'Non-aktif' }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-white/60 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-8 flex flex-col items-center justify-center text-center h-full min-h-[300px]">
                            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center text-slate-300 mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            </div>
                            <p class="text-slate-500">Hasil scan akan muncul di sini</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 4: PENERIMAAN BARANG -->
            <div x-show="tab === 'penerimaan_barang'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                
                <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-xl shadow-slate-200/50 mb-8">
                    <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Buat Purchase Order (PO)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                                    <th class="px-6 py-4">Item Disetujui Kaprodi</th>
                                    <th class="px-6 py-4">Ruangan</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($approvedItems as $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-800">{{ $item->name }}</p>
                                        <p class="text-xs text-slate-500">Disetujui: {{ $item->approved_at->format('d M Y') }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $item->room->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('staff_admin.po.store', $item) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                                                Buat PO Baru
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="px-6 py-8 text-center text-slate-500">Tidak ada pengajuan yang butuh PO.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-xl shadow-slate-200/50">
                    <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Daftar Purchase Order Aktif</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                                    <th class="px-6 py-4">Nomor PO & Item</th>
                                    <th class="px-6 py-4">Progress Diterima</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($purchaseOrders as $po)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-800">{{ $po->po_number }}</p>
                                        <p class="text-sm text-slate-600">{{ $po->inventoryItem->name ?? 'Unknown Item' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-indigo-500" style="width: {{ ($po->total_received / $po->total_ordered) * 100 }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-slate-600">{{ $po->total_received }}/{{ $po->total_ordered }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($po->status === 'completed')
                                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-700">Selesai</span>
                                        @elseif($po->status === 'partial')
                                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-700">Parsial</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-100 text-blue-700">Dipesan</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="showReceiptModal = true; activePo = {{ $po->id }}" class="text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-indigo-600 hover:text-white transition-colors border border-indigo-100">Catat Terima</button>
                                    </td>
                                </tr>

                                <!-- Modal Terima Barang -->
                                <div x-show="showReceiptModal && activePo === {{ $po->id }}" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showReceiptModal = false; activePo = null"></div>
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                                            <form action="{{ route('staff_admin.goods-receipt.store', $po) }}" method="POST">
                                                @csrf
                                                <div class="px-6 py-5 border-b border-slate-200"><h3 class="text-lg font-bold text-slate-800">Catat Penerimaan: {{ $po->po_number }}</h3></div>
                                                <div class="p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Diterima</label>
                                                        <input type="date" name="received_date" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200" value="{{ date('Y-m-d') }}" required>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah Diterima</label>
                                                        <input type="number" name="qty_received" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200" max="{{ $po->remaining }}" value="{{ $po->remaining }}" required>
                                                        <p class="text-xs text-slate-500 mt-1">Maksimal sisa yang bisa diterima: {{ $po->remaining }} unit</p>
                                                    </div>
                                                </div>
                                                <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 rounded-b-3xl">
                                                    <button type="button" @click="showReceiptModal = false; activePo = null" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 font-medium text-sm">Batal</button>
                                                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 font-medium text-sm">Simpan</button>
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

            <!-- TAB 5: UPDATE INVENTARIS MANUAL -->
            <div x-show="tab === 'update_inventaris'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-8 shadow-xl shadow-slate-200/50">
                    <h3 class="text-2xl font-bold text-slate-800 mb-2">Registrasi Inventaris Langsung</h3>
                    <p class="text-slate-500 mb-8">Formulir ini digunakan oleh Admin untuk menginput barang yang sudah ada langsung ke sistem (Bypass approval Kaprodi).</p>

                    <form action="{{ route('staff_admin.inventory.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Kode Label <span class="text-rose-500">*</span></label>
                                <input type="text" name="label_code" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                                <select name="category" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
                                    <option value="inventaris">Inventaris (Aset Tetap)</option>
                                    <option value="bhp">BHP (Barang Habis Pakai)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Ruangan</label>
                                <select name="room_id" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
                                    @foreach($rooms as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Harga (Opsional)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-slate-500 sm:text-sm">Rp</span>
                                    </div>
                                    <input type="number" name="price" class="w-full pl-10 rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Merk / Brand (Opsional)</label>
                                <input type="text" name="brand" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">QR Kampus (Opsional)</label>
                                <input type="text" name="qr_kampus" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200" placeholder="Contoh: QR-UNIV-123">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-200 flex justify-end">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-indigo-200 transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Daftarkan Barang
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 6: INVENTARIS & BHP -->
            <div x-show="tab === 'inventaris_bhp'" style="display: none;" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div x-data="{ innerTab: 'inventaris' }" class="space-y-6">
                    <div class="bg-white/60 backdrop-blur-xl border border-white/80 p-1.5 rounded-xl shadow-sm inline-flex space-x-1">
                        <button @click="innerTab = 'inventaris'" :class="innerTab === 'inventaris' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 rounded-lg font-medium text-xs transition-all duration-300">
                            Inventaris
                        </button>
                        <button @click="innerTab = 'bhp'" :class="innerTab === 'bhp' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 rounded-lg font-medium text-xs transition-all duration-300">
                            BHP
                        </button>
                    </div>

                    <!-- Inner Tab 1: Inventaris -->
                    <div x-show="innerTab === 'inventaris'" class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-200/60 bg-white/50">
                            <h3 class="text-xl font-bold text-slate-800">Daftar Inventaris</h3>
                            <p class="mt-1 text-sm text-slate-500">Daftar aset tetap yang aktif dalam sistem.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                                        <th class="px-8 py-4">Nama Barang</th>
                                        <th class="px-8 py-4">Kondisi</th>
                                        <th class="px-8 py-4">Ruangan</th>
                                        <th class="px-8 py-4">Harga</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($allInventaris as $item)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-8 py-4">
                                            <p class="font-bold text-slate-800">{{ $item->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $item->label_code ?? 'Belum dilabeli' }}</p>
                                        </td>
                                        <td class="px-8 py-4 text-sm text-slate-600">
                                            {{ $item->condition_label }}
                                        </td>
                                        <td class="px-8 py-4 text-sm text-slate-600">
                                            {{ $item->room->name ?? '-' }}
                                        </td>
                                        <td class="px-8 py-4 text-sm font-semibold text-slate-800">
                                            Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-8 py-12 text-center text-slate-500">
                                            Belum ada data inventaris.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Inner Tab 2: BHP -->
                    <div x-show="innerTab === 'bhp'" style="display: none;" class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-200/60 bg-white/50">
                            <h3 class="text-xl font-bold text-slate-800">Daftar BHP (Barang Habis Pakai)</h3>
                            <p class="mt-1 text-sm text-slate-500">Daftar barang habis pakai yang aktif dalam sistem.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                                        <th class="px-8 py-4">Nama BHP</th>
                                        <th class="px-8 py-4">Ruangan</th>
                                        <th class="px-8 py-4">Harga</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($allBhp as $item)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-8 py-4">
                                            <p class="font-bold text-slate-800">{{ $item->name }}</p>
                                        </td>
                                        <td class="px-8 py-4 text-sm text-slate-600">
                                            {{ $item->room->name ?? '-' }}
                                        </td>
                                        <td class="px-8 py-4 text-sm font-semibold text-slate-800">
                                            Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="px-8 py-12 text-center text-slate-500">
                                            Belum ada data BHP.
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
    </div>
</x-app-layout>
