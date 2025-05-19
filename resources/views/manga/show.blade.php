<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $manga->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            
            @if (session('info'))
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                    {{ session('info') }}
                </div>
            @endif
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-1/4 mb-4 md:mb-0 md:mr-6">
                            @if ($manga->cover_image)
                                <img src="{{ $manga->cover_image_url }}" alt="{{ $manga->title }}" class="w-full max-h-48 object-contain mx-auto rounded-lg shadow-md" onerror="this.onerror=null; this.src='{{ asset('images/no-cover.svg') }}'; this.alt='No Cover Available';">
                                
                                @auth
                                <div class="mt-2">
                                    <form method="POST" action="{{ route('manga.fetch.cover', $manga) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">
                                            Get cover from MyAnimeList
                                        </button>
                                    </form>
                                </div>
                                @endauth
                            @else
                                <div class="w-full h-48 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <span class="text-gray-500">No Cover</span>
                                </div>
                                
                                @auth
                                <div class="mt-2">
                                    <form method="POST" action="{{ route('manga.fetch.cover', $manga) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">
                                            Get cover from MyAnimeList
                                        </button>
                                    </form>
                                </div>
                                @endauth
                            @endif
                            
                                @auth
                                <div class="mt-4 flex flex-col space-y-2">
                                    <a href="{{ route('manga.edit', $manga) }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Edit Manga') }}
                                    </a>
                                    
                                    <form method="POST" action="{{ route('manga.fetch.chapters', $manga) }}" class="w-full">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            {{ __('Auto-Import Chapters') }}
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="{{ route('manga.destroy', $manga) }}" class="w-full">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="return confirm('Are you sure you want to delete this manga?')">
                                            {{ __('Delete Manga') }}
                                        </button>
                                    </form>
                                </div>
                                @endauth
                        </div>
                        
                        <div class="md:w-3/4">
                            <h1 class="text-2xl font-bold mb-2">{{ $manga->title }}</h1>
                            
                            <div class="mb-4">
                                <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">
                                    {{ ucfirst($manga->status) }}
                                </span>
                                
                                @if ($manga->author)
                                <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">
                                    Author: {{ $manga->author }}
                                </span>
                                @endif
                                
                                <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">
                                    {{ $manga->total_chapters }} {{ Str::plural('Chapter', $manga->total_chapters) }}
                                </span>
                                
                                @if ($manga->published_from)
                                <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">
                                    Published: {{ $manga->published_from->format('M d, Y') }}
                                    @if ($manga->published_to)
                                        to {{ $manga->published_to->format('M d, Y') }}
                                    @endif
                                </span>
                                @endif
                            </div>
                            
                            @if ($manga->description)
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-2">Description</h3>
                                <p class="text-gray-700">{{ $manga->description }}</p>
                            </div>
                            @endif
                            
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Chapters</h3>
                                
                                @if ($chapters->count() > 0)
                                    <div class="space-y-2">
                                        @foreach ($chapters as $chapter)
                                            <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                                                <a href="{{ route('manga.chapters.show', [$manga, $chapter]) }}" class="flex justify-between items-center">
                                                    <div>
                                                        <span class="font-medium">Chapter {{ $chapter->chapter_number }}</span>
                                                        @if ($chapter->title)
                                                            <span class="ml-2 text-gray-600">{{ $chapter->title }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $chapter->created_at->format('M d, Y') }}
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500">No chapters available yet.</p>
                                @endif
                                
                                @auth
                                <div class="mt-4">
                                    <a href="{{ route('manga.chapters.create', $manga) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Add New Chapter') }}
                                    </a>
                                </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="{{ route('manga.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Back to Manga List') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
