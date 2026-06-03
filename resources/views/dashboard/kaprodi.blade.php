<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Ketua Program Studi') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen" x-data="{ tab: '{{ request('tab', 'ringkasan') }}', selectedDraft: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            
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
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</h4>
                        <p class="mt-2 text-sm text-gray-500">Dari {{ $roomCount }} Laboratorium</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Total Draf</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">{{ $drafts->count() }}</h4>
                        <p class="mt-2 text-sm text-gray-500">Dari semua Kepala Lab</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">Pengajuan Perlu Review</p>
                        <h4 class="mt-2 text-3xl font-bold text-gray-900">{{ $pendingDrafts }}</h4>
                        <p class="mt-2 text-sm {{ $pendingDrafts > 0 ? 'text-amber-600 font-medium' : 'text-gray-500' }}">
                            {{ $pendingDrafts > 0 ? 'Menunggu Persetujuan' : 'Semua sudah direview' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- TAB 2: REVIEW DRAF -->
            <div x-show="tab === 'review_draf'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @foreach($drafts as $draft)
                @if($draft->items->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ $draft->draft_number }}
                                <span class="text-sm text-gray-500 font-normal">— oleh {{ $draft->user->name }}</span>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $draft->room->name ?? 'Tanpa Lab' }} • {{ $draft->created_at->format('d M Y') }}
                                @if($draft->status === 'locked')
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-800 border border-gray-200 ml-2">Locked</span>
                                @endif
                            </p>
                        </div>
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
                                @foreach($draft->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                        @if($item->link)
                                            <div class="text-sm text-indigo-600 hover:text-indigo-900"><a href="{{ $item->link }}" target="_blank">Lihat Katalog</a></div>
                                        @endif
                                        @if($item->replacesInventory)
                                            <div class="text-xs text-gray-500 mt-1">Menggantikan: {{ $item->replacesInventory->label_code }} - {{ $item->replacesInventory->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($item->review_status === 'approved')
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-medium rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Disetujui
                                            </span>
                                        @elseif($item->review_status === 'rejected')
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-medium rounded-full bg-rose-100 text-rose-800 border border-rose-200">
                                                Ditolak
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-medium rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                Menunggu Review
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        @if($draft->status !== 'locked' && $item->review_status === 'pending')
                                            <form action="{{ route('kaprodi.items.approve', $item) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-emerald-600 hover:text-emerald-900 mx-1 font-semibold">Setujui</button>
                                            </form>
                                            <form action="{{ route('kaprodi.items.reject', $item) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-rose-600 hover:text-rose-900 mx-1 font-semibold">Tolak</button>
                                            </form>
                                        @elseif($draft->status !== 'locked' && $item->review_status === 'approved')
                                            <span class="text-gray-400 mx-1">Setujui</span>
                                            <form action="{{ route('kaprodi.items.reject', $item) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-rose-600 hover:text-rose-900 mx-1 font-semibold">Batalkan</button>
                                            </form>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                @endforeach

                @if($drafts->flatMap->items->count() === 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                    <p class="text-gray-500">Belum ada item pengadaan untuk direview.</p>
                </div>
                @endif
            </div>

            <!-- TAB 3: FINALISASI DRAF -->
            <div x-show="tab === 'finalisasi'" class="space-y-6" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @php $draftDrafts = $drafts->where('status', 'draft'); @endphp
                
                @if($draftDrafts->count() > 0)
                @foreach($draftDrafts as $draft)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 max-w-3xl mx-auto">
                    
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 mb-4">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    
                    <h3 class="text-xl font-medium text-gray-900 text-center">Finalisasi {{ $draft->draft_number }}</h3>
                    <p class="mt-1 text-sm text-gray-500 text-center">{{ $draft->room->name ?? '' }} • {{ $draft->user->name }}</p>
                    <p class="mt-2 text-sm text-gray-500 text-center">Mengunci draf akan meneruskannya secara otomatis ke Staf Administrasi. Data tidak akan bisa diubah lagi oleh siapapun setelah ini.</p>
                    
                    <div class="mt-6 bg-gray-50 p-6 rounded-md border border-gray-200">
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Total Item</p>
                                <p class="font-semibold text-gray-900">{{ $draft->items->count() }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Disetujui</p>
                                <p class="font-semibold text-emerald-600">{{ $draft->items->where('review_status', 'approved')->count() }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Ditolak</p>
                                <p class="font-semibold text-rose-600">{{ $draft->items->where('review_status', 'rejected')->count() }}</p>
                            </div>
                        </div>
                        
                        @if($draft->items->where('review_status', 'pending')->count() > 0)
                        <div class="mt-4 flex bg-yellow-50 p-4 border border-yellow-200 rounded-md">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Peringatan</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Ada {{ $draft->items->where('review_status', 'pending')->count() }} item yang belum direview. Item tersebut tidak akan diteruskan ke Staf Administrasi.</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($draft->items->where('review_status', 'rejected')->count() > 0)
                        <div class="mt-4 flex bg-rose-50 p-4 border border-rose-200 rounded-md">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-rose-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-rose-800">Info</h3>
                                <div class="mt-2 text-sm text-rose-700">
                                    <p>Ada {{ $draft->items->where('review_status', 'rejected')->count() }} item yang ditolak. Item tersebut tidak akan diteruskan.</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="mt-4 text-sm text-right">
                            <p class="text-gray-700">Total Disetujui: <strong>Rp {{ number_format($draft->approved_total, 0, ',', '.') }}</strong></p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <form action="{{ route('kaprodi.drafts.finalize', $draft) }}" method="POST" onsubmit="return confirm('Yakin ingin mengunci draf ini secara permanen?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Finalisasi & Kunci Draf Permanen
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
                @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center max-w-3xl mx-auto">
                    <p class="text-gray-500">Tidak ada draf yang perlu difinalisasi. Semua draf sudah dikunci atau belum ada draf.</p>
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
