<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('MangaDex API Test') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-4">
                        <a href="{{ route('manga.import.form') }}" class="text-blue-500 hover:text-blue-700">
                            &larr; Back to Import
                        </a>
                    </div>

                    @if($success)
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                            <p class="font-bold">Success!</p>
                            <p>{{ $message }}</p>
                        </div>
                        
                        @if(isset($imagePath))
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-2">Test Image:</h3>
                                <img src="{{ asset('storage/' . $imagePath) }}" alt="Test Image" class="max-w-md border">
                            </div>
                        @endif
                    @else
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                            <p class="font-bold">Error!</p>
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-2">API Response:</h3>
                        <div class="bg-gray-100 p-4 rounded overflow-auto max-h-96">
                            <pre class="text-xs">{{ $response }}</pre>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-2">Troubleshooting:</h3>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Make sure your internet connection is working properly</li>
                            <li>Check if MangaDex API is currently available</li>
                            <li>Verify that the manga and chapters you're trying to access are publicly available</li>
                            <li>Some manga may require authentication to access</li>
                            <li>The API structure might have changed since this application was developed</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
