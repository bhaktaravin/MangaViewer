<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Personal Information') }}</h3>
                    <div class="mt-4 space-y-2">
                        <div>
                            <span class="font-medium">{{ __('Name') }}:</span> {{ Auth::user()->name }}
                        </div>
                        <div>
                            <span class="font-medium">{{ __('Email') }}:</span> {{ Auth::user()->email }}
                        </div>
                        <div>
                            <span class="font-medium">{{ __('Member Since') }}:</span> {{ Auth::user()->created_at->format('F j, Y') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Manga Management') }}</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <a href="{{ route('manga.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Add New Manga') }}
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('manga.import.form') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Import from MangaDex') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('My Recent Activity') }}</h3>
                    <div class="mt-4">
                        <!-- This section can be expanded later to show user's reading history, uploads, etc. -->
                        <p>{{ __('Your recent activity will appear here.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
