<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manga Library') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900">Your Manga Collection</h3>
                
                @auth
                <a href="{{ route('manga.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Add New Manga') }}
                </a>
                @endauth
            </div>
            
            @if ($mangas->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($mangas as $manga)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                            <a href="{{ route('manga.show', $manga) }}" class="block">
                                <div class="h-40 bg-gray-100 flex items-center justify-center overflow-hidden">
                                    @if ($manga->cover_image)
                                        <img src="{{ $manga->cover_image_url }}" alt="{{ $manga->title }}" class="h-full w-auto object-contain" onerror="this.onerror=null; this.src='{{ asset('images/no-cover.svg') }}'; this.alt='No Cover Available';">
                                    @else
                                        <div class="w-24 h-36 bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-500 text-sm">No Cover</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <h4 class="font-semibold text-gray-900 truncate">{{ $manga->title }}</h4>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-xs text-gray-500">{{ $manga->total_chapters }} {{ Str::plural('Chapter', $manga->total_chapters) }}</span>
                                        <span class="text-xs px-2 py-1 bg-gray-100 rounded-full">{{ ucfirst($manga->status) }}</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-6">
                    {{ $mangas->links() }}
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <p class="mb-4">Your manga collection is empty.</p>
                        <p>
                            @auth
                                <a href="{{ route('manga.create') }}" class="text-indigo-600 hover:text-indigo-900">Add your first manga</a>
                                or import from 
                                <a href="{{ route('manga.import.form') }}" class="text-indigo-600 hover:text-indigo-900">MangaDex</a>,
                                <a href="{{ route('manga.sources.mangasee.search.form') }}" class="text-indigo-600 hover:text-indigo-900">MangaSee</a>,
                                <a href="{{ route('manga.sources.mangalife.search.form') }}" class="text-indigo-600 hover:text-indigo-900">MangaLife</a>
                            @else
                                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-900">Log in</a> to add manga to your collection
                            @endauth
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
