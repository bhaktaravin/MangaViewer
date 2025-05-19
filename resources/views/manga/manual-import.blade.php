<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manual Manga Import') }}
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

                    <form method="POST" action="{{ route('manga.manual.process') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <x-input-label for="title" :value="__('Manga Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="author" :value="__('Author (Optional)')" />
                            <x-text-input id="author" class="block mt-1 w-full" type="text" name="author" :value="old('author')" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Description (Optional)')" />
                            <textarea id="description" name="description" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="4">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="cover_image" :value="__('Cover Image (Optional)')" />
                            <input id="cover_image" type="file" name="cover_image" class="block mt-1 w-full" accept="image/*">
                        </div>

                        <div class="mb-4">
                            <x-input-label for="files" :value="__('Chapter Files (ZIP/RAR)')" />
                            <input id="files" type="file" name="files[]" class="block mt-1 w-full" multiple required>
                            <p class="text-sm text-gray-500 mt-1">Upload ZIP/RAR files containing manga chapters. Each file should represent one chapter.</p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('manga.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Import') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
