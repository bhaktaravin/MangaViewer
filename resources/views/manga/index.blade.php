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
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('All Manga') }}</h3>
                        
                        @auth
                        <div class="flex space-x-2">
                            <a href="{{ route('manga.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Add New Manga') }}
                            </a>
                            
                            <a href="{{ route('manga.import.form') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Import Manga') }}
                            </a>
                        </div>
                        @endauth
                    </div>
                    
                    @if ($mangas->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            @foreach ($mangas as $manga)
                                <div class="border rounded-lg overflow-hidden shadow-md hover:shadow-lg transition">
                                    <a href="{{ route('manga.show', $manga) }}" class="block">
                                        <div class="h-64 overflow-hidden">
                                            @if ($manga->cover_image)
                                                <img src="{{ asset('storage/' . $manga->cover_image) }}" alt="{{ $manga->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                    <span class="text-gray-500">No Cover</span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="p-4">
                                            <h4 class="font-semibold text-lg mb-1 truncate">{{ $manga->title }}</h4>
                                            
                                            <div class="flex justify-between text-sm text-gray-600">
                                                <span>{{ ucfirst($manga->status) }}</span>
                                                <span>{{ $manga->total_chapters }} {{ Str::plural('Ch', $manga->total_chapters) }}</span>
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
                        <div class="text-center py-8">
                            <p class="text-gray-500 mb-4">{{ __('No manga available yet.') }}</p>
                            
                            @auth
                            <div class="flex justify-center space-x-4">
                                <a href="{{ route('manga.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Add Your First Manga') }}
                                </a>
                                
                                <a href="{{ route('manga.import.form') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Import from MangaDex') }}
                                </a>
                            </div>
                            @else
                            <p>
                                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800">Log in</a> or 
                                <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800">register</a> 
                                to add manga to the library.
                            </p>
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
