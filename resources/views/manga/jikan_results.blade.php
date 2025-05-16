<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('MyAnimeList Search Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Search Results') }}</h3>
                    
                    @if (count($results) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($results as $manga)
                                <div class="border rounded-lg overflow-hidden shadow-md">
                                    <div class="p-4">
                                        <h4 class="font-semibold text-lg mb-2">
                                            {{ $manga['title'] }}
                                        </h4>
                                        
                                        <div class="mb-3">
                                            <img src="{{ $manga['images']['jpg']['large_image_url'] ?? 'https://via.placeholder.com/256x384?text=No+Cover' }}" alt="Cover" class="w-full h-64 object-cover">
                                        </div>
                                        
                                        <div class="mb-3 text-sm">
                                            <p class="line-clamp-3">
                                                {{ $manga['synopsis'] ?? 'No description available.' }}
                                            </p>
                                        </div>
                                        
                                        <div class="mb-3 text-sm">
                                            <p><strong>Type:</strong> {{ $manga['type'] ?? 'Unknown' }}</p>
                                            <p><strong>Status:</strong> {{ $manga['status'] ?? 'Unknown' }}</p>
                                            <p><strong>Chapters:</strong> {{ $manga['chapters'] ?? 'Unknown' }}</p>
                                            <p><strong>Score:</strong> {{ $manga['score'] ?? 'N/A' }}/10</p>
                                        </div>
                                        
                                        <!-- IMPORT BUTTON - START -->
                                        <div class="mt-4 mb-4 bg-green-100 p-2 rounded-lg border border-green-300">
                                            <form method="POST" action="{{ route('manga.jikan.import') }}">
                                                @csrf
                                                <input type="hidden" name="manga_id" value="{{ $manga['mal_id'] }}">
                                                <button type="submit" class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-bold hover:bg-green-700 transition">
                                                    IMPORT METADATA
                                                </button>
                                            </form>
                                        </div>
                                        <!-- IMPORT BUTTON - END -->
                                        
                                        <div>
                                            <a href="{{ $manga['url'] }}" target="_blank" class="w-full bg-gray-600 text-white py-2 px-4 rounded text-center block">
                                                {{ __('View on MyAnimeList') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p>{{ __('No results found. Please try a different search term.') }}</p>
                        </div>
                    @endif
                    
                    <div class="mt-6">
                        <a href="{{ route('manga.jikan.form') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Back to Search') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
