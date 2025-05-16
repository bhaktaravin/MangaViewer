<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Manga from MangaDex') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Search MangaDex') }}</h3>
                    
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <p class="mb-4">
                        Search for manga on MangaDex and import them directly to your local database. 
                        This will download the manga cover and chapters, making them available offline.
                    </p>
                    
                    <div class="mb-4">
                        <a href="{{ route('manga.test.image') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Test Image Download') }}
                        </a>
                    </div>

                    <form method="POST" action="{{ route('manga.search') }}">
                        @csrf
                        
                        <div>
                            <x-input-label for="search" :value="__('Search for manga')" />
                            <x-text-input id="search" class="block mt-1 w-full" type="text" name="search" :value="old('search')" required autofocus />
                            <x-input-error :messages="$errors->get('search')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-3">
                                {{ __('Search') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Manual Import') }}</h3>
                    <p class="mb-4">{{ __('Prefer to import your own manga files? Use our manual import tool.') }}</p>
                    
                    <a href="{{ route('manga.manual.import') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Manual Import') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
