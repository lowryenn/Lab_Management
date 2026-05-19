<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Staff Lab') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}</h3>
                    <p>{{ __("Anda login sebagai Staff Lab.") }}</p>
                    
                    <!-- Area template -->
                    <div class="mt-6 p-6 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                        <p class="text-gray-500 text-center"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
