<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Create Page') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                <form action="{{ route('dashboard.pages.store') }}" method="POST" class="space-y-5">
                    @csrf

                    @include('pages.partials.form', ['page' => null, 'submitLabel' => __('Save Page')])
                </form>
            </div>
        </div>
    </div>

    @include('pages.partials.editor')
</x-app-layout>
