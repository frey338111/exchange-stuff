<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-8 px-4 sm:grid-cols-2 sm:px-0 lg:grid-cols-4 lg:gap-10">
                @foreach ($dashboardCards as $card)
                    <section
                        aria-label="{{ $card['label'] }}"
                        class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="py-10 pr-10 sm:py-12 sm:pr-12" style="padding-left: 4rem;">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ $card['label'] }}
                            </p>
                            <p class="mt-3 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $card['value'] }}
                            </p>
                            <p class="mt-2 text-sm {{ $card['noteClass'] }}">
                                {{ $card['note'] }}
                            </p>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
