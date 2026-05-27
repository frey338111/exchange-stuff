<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Customers') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex flex-col gap-4 p-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Customer list') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Search registered customers by name or email.') }}
                        </p>
                    </div>

                    <form method="GET" action="{{ route('customers.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
                        <input type="hidden" name="sort" value="{{ $sort }}">
                        <input type="hidden" name="direction" value="{{ $direction }}">

                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Search') }}
                            </label>
                            <input
                                id="search"
                                name="search"
                                type="search"
                                value="{{ $search }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 sm:w-80"
                                placeholder="{{ __('Name or email') }}"
                            >
                        </div>

                        <div class="flex gap-2">
                            <x-primary-button type="submit">
                                {{ __('Search') }}
                            </x-primary-button>
                            <a
                                href="{{ route('customers.index') }}"
                                class="inline-flex items-center justify-center rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                            >
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </form>
                </div>

                @php
                    $sortUrl = fn (string $column) => route('customers.index', array_filter([
                        'search' => $search,
                        'sort' => $column,
                        'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
                    ], fn ($value) => filled($value)));
                    $sortLabel = fn (string $column) => $sort === $column ? ($direction === 'asc' ? ' (asc)' : ' (desc)') : '';
                    $sortClass = 'inline-flex items-center text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200';
                @endphp

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('customer_id') }}" class="{{ $sortClass }}">ID{{ $sortLabel('customer_id') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('name') }}" class="{{ $sortClass }}">Name{{ $sortLabel('name') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('email') }}" class="{{ $sortClass }}">Email{{ $sortLabel('email') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('phone') }}" class="{{ $sortClass }}">Phone{{ $sortLabel('phone') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('balance') }}" class="{{ $sortClass }}">Balance{{ $sortLabel('balance') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('status') }}" class="{{ $sortClass }}">Status{{ $sortLabel('status') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('created_at') }}" class="{{ $sortClass }}">Registered{{ $sortLabel('created_at') }}</a>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse ($customers as $customer)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        #{{ $customer->customer_id }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $customer->name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $customer->email }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $customer->phone ?: '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $customer->balance }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $customer->status ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                            {{ $customer->status ? __('Active') : __('Inactive') }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $customer->created_at?->format('M j, Y g:i A') ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('No customers found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($customers->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        {{ $customers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
