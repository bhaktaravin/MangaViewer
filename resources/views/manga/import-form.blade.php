<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Manga from MangaDex') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6">
                        <p class="mb-2">Import manga directly from MangaDex by searching for titles.</p>
                        <div class="flex space-x-4">
                            <a href="{{ route('manga.manual.import') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Manual Import') }}
                            </a>
                            <a href="{{ route('manga.jikan.form') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('MyAnimeList Import') }}
                            </a>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('manga.search') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <x-input-label for="search" :value="__('Search MangaDex')" />
                            <div class="flex">
                                <x-text-input id="search" class="block mt-1 w-full" type="text" name="search" :value="old('search')" required autofocus placeholder="Enter manga title..." />
                                <x-primary-button class="ml-3 mt-1">
                                    {{ __('Search') }}
                                </x-primary-button>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Enter at least 3 characters to search.</p>
                        </div>
                    </form>

                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">API Status</h3>
                        <div id="api-status" class="p-4 bg-gray-100 rounded">
                            <p>Checking MangaDex API status...</p>
                        </div>
                        <button id="test-api" class="mt-2 inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Test API Connection') }}
                        </button>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const testApiButton = document.getElementById('test-api');
                            const apiStatus = document.getElementById('api-status');
                            
                            testApiButton.addEventListener('click', function() {
                                apiStatus.innerHTML = '<p>Testing connection...</p>';
                                
                                fetch('{{ route("manga.test.api") }}')
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            apiStatus.innerHTML = '<p class="text-green-600">✓ MangaDex API connection successful</p>';
                                        } else {
                                            apiStatus.innerHTML = '<p class="text-red-600">✗ MangaDex API connection failed</p>';
                                        }
                                    })
                                    .catch(error => {
                                        apiStatus.innerHTML = '<p class="text-red-600">✗ Error testing API connection</p>';
                                        console.error('Error:', error);
                                    });
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
