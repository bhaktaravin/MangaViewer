<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $manga->title }} - Chapter {{ $chapter->chapter_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold mb-2">
                            Chapter {{ $chapter->chapter_number }}: {{ $chapter->title }}
                        </h1>
                        
                        <div class="flex justify-between items-center">
                            <div>
                                @if ($chapter->chapter_number > 1)
                                    @php
                                        $prevChapter = $manga->chapters()->where('chapter_number', '<', $chapter->chapter_number)->orderBy('chapter_number', 'desc')->first();
                                    @endphp
                                    
                                    @if ($prevChapter)
                                        <a href="{{ route('manga.chapters.show', [$manga, $prevChapter]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            &larr; Previous Chapter
                                        </a>
                                    @endif
                                @endif
                            </div>
                            
                            <div>
                                @php
                                    $nextChapter = $manga->chapters()->where('chapter_number', '>', $chapter->chapter_number)->orderBy('chapter_number')->first();
                                @endphp
                                
                                @if ($nextChapter)
                                    <a href="{{ route('manga.chapters.show', [$manga, $nextChapter]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Next Chapter &rarr;
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if ($pages->count() > 0)
                        <div class="space-y-4">
                            @foreach ($pages as $page)
                                <div class="flex justify-center">
                                    <img src="{{ asset('storage/' . $page->image_path) }}" alt="Page {{ $page->page_number }}" class="max-w-full">
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No pages available for this chapter.</p>
                        </div>
                    @endif
                    
                    <div class="mt-6 flex justify-between items-center">
                        <div>
                            @if ($chapter->chapter_number > 1)
                                @php
                                    $prevChapter = $manga->chapters()->where('chapter_number', '<', $chapter->chapter_number)->orderBy('chapter_number', 'desc')->first();
                                @endphp
                                
                                @if ($prevChapter)
                                    <a href="{{ route('manga.chapters.show', [$manga, $prevChapter]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        &larr; Previous Chapter
                                    </a>
                                @endif
                            @endif
                        </div>
                        
                        <div>
                            <a href="{{ route('manga.show', $manga) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Back to Manga
                            </a>
                        </div>
                        
                        <div>
                            @php
                                $nextChapter = $manga->chapters()->where('chapter_number', '>', $chapter->chapter_number)->orderBy('chapter_number')->first();
                            @endphp
                            
                            @if ($nextChapter)
                                <a href="{{ route('manga.chapters.show', [$manga, $nextChapter]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Next Chapter &rarr;
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            @auth
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Chapter Management</h3>
                    
                    <div class="flex space-x-4">
                        <a href="{{ route('manga.chapters.edit', [$manga, $chapter]) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit Chapter
                        </a>
                        
                        <a href="{{ route('manga.chapters.pages.index', [$manga, $chapter]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Manage Pages
                        </a>
                        
                        <form method="POST" action="{{ route('manga.chapters.destroy', [$manga, $chapter]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="return confirm('Are you sure you want to delete this chapter?')">
                                Delete Chapter
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </div>
</x-app-layout>
