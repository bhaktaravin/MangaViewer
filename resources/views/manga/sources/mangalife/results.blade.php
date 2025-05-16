<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('MangaLife Search Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-4">
                        <a href="{{ route('manga.sources.mangalife.search.form') }}" class="text-blue-500 hover:text-blue-700">
                            &larr; Back to Search
                        </a>
                    </div>

                    @if(empty($results))
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                            <p>No results found or an error occurred. Please try again.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($results as $manga)
                                <div class="border rounded-lg overflow-hidden shadow-lg">
                                    @if(isset($manga['cover']))
                                        <img src="{{ $manga['cover'] }}" alt="{{ $manga['title'] }}" class="w-full h-64 object-cover">
                                    @else
                                        <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-500">No Cover</span>
                                        </div>
                                    @endif
                                    <div class="p-4">
                                        <h3 class="font-bold text-lg mb-2">{{ $manga['title'] }}</h3>
                                        <p class="text-gray-700 text-sm mb-2">
                                            <span class="font-semibold">Author:</span> {{ $manga['author'] ?? 'Unknown' }}
                                        </p>
                                        <p class="text-gray-700 text-sm mb-4">
                                            <span class="font-semibold">Status:</span> {{ ucfirst($manga['status'] ?? 'Unknown') }}
                                        </p>
                                        <form method="POST" action="{{ route('manga.sources.mangalife.import') }}">
                                            @csrf
                                            <input type="hidden" name="manga_slug" value="{{ $manga['slug'] }}">
                                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                                                Import
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
