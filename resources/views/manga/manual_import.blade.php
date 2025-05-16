<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manual Manga Import') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Import Manga Files') }}</h3>
                    
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('manga.manual.process') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <x-input-label for="title" :value="__('Manga Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="4">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="author" :value="__('Author')" />
                            <x-text-input id="author" class="block mt-1 w-full" type="text" name="author" :value="old('author')" />
                            <x-input-error :messages="$errors->get('author')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="hiatus" {{ old('status') == 'hiatus' ? 'selected' : '' }}>Hiatus</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="cover_image" :value="__('Cover Image')" />
                            <input id="cover_image" type="file" name="cover_image" class="block mt-1 w-full" accept="image/*" />
                            <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <h4 class="font-medium text-gray-700 mb-2">{{ __('Chapters') }}</h4>
                            
                            <div id="chapters-container">
                                <div class="chapter-entry border p-4 rounded mb-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <x-input-label for="chapter_numbers[0]" :value="__('Chapter Number')" />
                                            <x-text-input id="chapter_numbers[0]" class="block mt-1 w-full" type="number" name="chapter_numbers[]" min="0" value="1" required />
                                        </div>
                                        
                                        <div>
                                            <x-input-label for="chapter_titles[0]" :value="__('Chapter Title')" />
                                            <x-text-input id="chapter_titles[0]" class="block mt-1 w-full" type="text" name="chapter_titles[]" value="Chapter 1" required />
                                        </div>
                                        
                                        <div>
                                            <x-input-label for="chapter_files[0]" :value="__('Chapter File (ZIP/RAR)')" />
                                            <input id="chapter_files[0]" type="file" name="chapter_files[]" class="block mt-1 w-full" accept=".zip,.rar,.7z" required />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" id="add-chapter" class="mt-2 px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
                                {{ __('+ Add Another Chapter') }}
                            </button>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('manga.import.form') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                {{ __('Cancel') }}
                            </a>
                            
                            <x-primary-button>
                                {{ __('Import Manga') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chaptersContainer = document.getElementById('chapters-container');
            const addChapterButton = document.getElementById('add-chapter');
            let chapterCount = 1;
            
            addChapterButton.addEventListener('click', function() {
                const newChapter = document.createElement('div');
                newChapter.className = 'chapter-entry border p-4 rounded mb-4';
                newChapter.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="chapter_numbers[${chapterCount}]" class="block font-medium text-sm text-gray-700">Chapter Number</label>
                            <input id="chapter_numbers[${chapterCount}]" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="number" name="chapter_numbers[]" min="0" value="${chapterCount + 1}" required />
                        </div>
                        
                        <div>
                            <label for="chapter_titles[${chapterCount}]" class="block font-medium text-sm text-gray-700">Chapter Title</label>
                            <input id="chapter_titles[${chapterCount}]" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="text" name="chapter_titles[]" value="Chapter ${chapterCount + 1}" required />
                        </div>
                        
                        <div>
                            <label for="chapter_files[${chapterCount}]" class="block font-medium text-sm text-gray-700">Chapter File (ZIP/RAR)</label>
                            <input id="chapter_files[${chapterCount}]" type="file" name="chapter_files[]" class="block mt-1 w-full" accept=".zip,.rar,.7z" required />
                        </div>
                    </div>
                    <button type="button" class="remove-chapter mt-2 px-3 py-1 bg-red-100 text-red-800 rounded hover:bg-red-200 transition">Remove</button>
                `;
                
                chaptersContainer.appendChild(newChapter);
                chapterCount++;
                
                // Add event listener to the remove button
                const removeButton = newChapter.querySelector('.remove-chapter');
                removeButton.addEventListener('click', function() {
                    chaptersContainer.removeChild(newChapter);
                });
            });
        });
    </script>
</x-app-layout>
