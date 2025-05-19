<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Manga') }}
            </h2>
            <a href="{{ route('manga.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                {{ __('Back to Library') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('manga.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <!-- Title -->
                                <div class="mb-4">
                                    <x-input-label for="title" :value="__('Title')" />
                                    <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>

                                <!-- Author -->
                                <div class="mb-4">
                                    <x-input-label for="author" :value="__('Author')" />
                                    <x-text-input id="author" class="block mt-1 w-full" type="text" name="author" :value="old('author')" />
                                    <x-input-error :messages="$errors->get('author')" class="mt-2" />
                                </div>

                                <!-- Status -->
                                <div class="mb-4">
                                    <x-input-label for="status" :value="__('Status')" />
                                    <select id="status" name="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                        <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="hiatus" {{ old('status') == 'hiatus' ? 'selected' : '' }}>Hiatus</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                </div>

                                <!-- Cover Image -->
                                <div class="mb-4">
                                    <x-input-label for="cover_image" :value="__('Cover Image')" />
                                    <input id="cover_image" type="file" name="cover_image" class="block mt-1 w-full" accept="image/*">
                                    <p class="text-sm text-gray-500 mt-1">Recommended size: 400x600 pixels</p>
                                    <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
                                </div>
                                
                                <!-- Cover Image URL -->
                                <div class="mb-4">
                                    <x-input-label for="cover_image_url" :value="__('Or Cover Image URL')" />
                                    <x-text-input id="cover_image_url" class="block mt-1 w-full" type="url" name="cover_image_url" :value="old('cover_image_url')" placeholder="https://example.com/image.jpg" />
                                    <p class="text-sm text-gray-500 mt-1">Enter a direct URL to an image</p>
                                    <x-input-error :messages="$errors->get('cover_image_url')" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <!-- Description -->
                                <div class="mb-4">
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" name="description" rows="10" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">{{ old('description') }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Create Manga') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
