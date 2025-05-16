<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Manga') }}: {{ $manga->title }}
            </h2>
            <a href="{{ route('manga.show', $manga) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                {{ __('Back to Manga') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('manga.update', $manga) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <!-- Title -->
                                <div class="mb-4">
                                    <x-input-label for="title" :value="__('Title')" />
                                    <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $manga->title)" required autofocus />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>

                                <!-- Author -->
                                <div class="mb-4">
                                    <x-input-label for="author" :value="__('Author')" />
                                    <x-text-input id="author" class="block mt-1 w-full" type="text" name="author" :value="old('author', $manga->author)" />
                                    <x-input-error :messages="$errors->get('author')" class="mt-2" />
                                </div>

                                <!-- Status -->
                                <div class="mb-4">
                                    <x-input-label for="status" :value="__('Status')" />
                                    <select id="status" name="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                        <option value="ongoing" {{ old('status', $manga->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                        <option value="completed" {{ old('status', $manga->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="hiatus" {{ old('status', $manga->status) == 'hiatus' ? 'selected' : '' }}>Hiatus</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                </div>

                                <!-- Cover Image -->
                                <div class="mb-4">
                                    <x-input-label for="cover_image" :value="__('Cover Image')" />
                                    
                                    @if($manga->cover_image)
                                        <div class="mt-2 mb-2">
                                            <img src="{{ asset('storage/' . $manga->cover_image) }}" alt="{{ $manga->title }}" class="w-32 h-auto">
                                            <p class="text-sm text-gray-500 mt-1">Current cover image</p>
                                        </div>
                                    @endif
                                    
                                    <input id="cover_image" type="file" name="cover_image" class="block mt-1 w-full" accept="image/*">
                                    <p class="text-sm text-gray-500 mt-1">Leave empty to keep current image</p>
                                    <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <!-- Description -->
                                <div class="mb-4">
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" name="description" rows="10" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">{{ old('description', $manga->description) }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Update Manga') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
