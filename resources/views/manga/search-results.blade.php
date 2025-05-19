<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Search Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Search results for: "{{ $search }}"</h3>
                        <a href="{{ route('manga.import.form') }}" class="text-blue-600 hover:underline">
                            ← Back to search
                        </a>
                    </div>

                    @if (count($results) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($results as $manga)
                                <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex h-48 bg-gray-100">
                                        @if (isset($manga['coverUrl']) && $manga['coverUrl'])
                                            <img src="{{ $manga['coverUrl'] }}" alt="{{ $manga['title'] }}" class="h-full w-auto object-cover mx-auto">
                                        @else
                                            <div class="flex items-center justify-center w-full">
                                                <span class="text-gray-400">No cover available</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        <h4 class="font-semibold text-lg mb-2 truncate" title="{{ $manga['title'] }}">{{ $manga['title'] }}</h4>
                                        
                                        @if (isset($manga['author']) && $manga['author'])
                                            <p class="text-sm text-gray-600 mb-2">{{ $manga['author'] }}</p>
                                        @endif
                                        
                                        @if (isset($manga['description']) && $manga['description'])
                                            <p class="text-sm text-gray-600 mb-4 line-clamp-3">{{ $manga['description'] }}</p>
                                        @endif
                                        
                                        <form method="POST" action="{{ route('manga.import') }}">
                                            @csrf
                                            <input type="hidden" name="manga_id" value="{{ $manga['id'] }}">
                                            <x-primary-button>
                                                {{ __('Import') }}
                                            </x-primary-button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No results found for "{{ $search }}". Try a different search term.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
