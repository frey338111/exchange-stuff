<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{
                match ($status) {
                    'live' => __('Live Listings'),
                    'rejected' => __('Rejected Listings'),
                    'requested' => __('Requested Listings'),
                    'accepted' => __('Accepted Listings'),
                    default => __('Pending Listings'),
                }
            }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/40 dark:text-green-200">
                    {{ session('status') }}
                </div>
            @endif

            @php
                $statusTitles = [
                    'pending' => __('Pending Listings'),
                    'live' => __('Live Listings'),
                    'rejected' => __('Rejected Listings'),
                    'requested' => __('Requested Listings'),
                    'accepted' => __('Accepted Listings'),
                ];
                $statusDescriptions = [
                    'pending' => __('Customer listings waiting for approval.'),
                    'live' => __('Approved customer listings.'),
                    'rejected' => __('Customer listings that were rejected.'),
                    'requested' => __('Listings with customer claim requests.'),
                    'accepted' => __('Listings with accepted claim requests.'),
                ];
                $statusEmptyMessages = [
                    'pending' => __('No pending listings found.'),
                    'live' => __('No live listings found.'),
                    'rejected' => __('No rejected listings found.'),
                    'requested' => __('No requested listings found.'),
                    'accepted' => __('No accepted listings found.'),
                ];
            @endphp

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ $statusTitles[$status] }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $statusDescriptions[$status] }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('listings.index', array_merge(['status' => 'pending'], array_filter($filters, fn ($value) => filled($value)))) }}"
                            class="rounded-md px-3 py-2 text-sm font-medium {{ $status === 'pending' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }}"
                        >
                            {{ __('Pending') }}
                        </a>
                        <a
                            href="{{ route('listings.index', array_merge(['status' => 'live'], array_filter($filters, fn ($value) => filled($value)))) }}"
                            class="rounded-md px-3 py-2 text-sm font-medium {{ $status === 'live' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }}"
                        >
                            {{ __('Live') }}
                        </a>
                        <a
                            href="{{ route('listings.index', array_merge(['status' => 'rejected'], array_filter($filters, fn ($value) => filled($value)))) }}"
                            class="rounded-md px-3 py-2 text-sm font-medium {{ $status === 'rejected' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }}"
                        >
                            {{ __('Rejected') }}
                        </a>
                        <a
                            href="{{ route('listings.index', array_merge(['status' => 'requested'], array_filter($filters, fn ($value) => filled($value)))) }}"
                            class="rounded-md px-3 py-2 text-sm font-medium {{ $status === 'requested' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }}"
                        >
                            {{ __('Requested') }}
                        </a>
                        <a
                            href="{{ route('listings.index', array_merge(['status' => 'accepted'], array_filter($filters, fn ($value) => filled($value)))) }}"
                            class="rounded-md px-3 py-2 text-sm font-medium {{ $status === 'accepted' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }}"
                        >
                            {{ __('Accepted') }}
                        </a>
                    </div>
                </div>

                <form method="GET" action="{{ route('listings.index') }}" class="border-t border-gray-200 p-6 dark:border-gray-700">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">

                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:gap-3">
                        <div class="min-w-0 lg:flex-1">
                            <label for="product_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Product name') }}
                            </label>
                            <input
                                id="product_name"
                                name="product_name"
                                type="search"
                                value="{{ $filters['product_name'] }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                placeholder="{{ __('Search products') }}"
                            >
                        </div>

                        <div class="min-w-0 lg:w-52">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Category') }}
                            </label>
                            <select
                                id="category_id"
                                name="category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            >
                                <option value="">{{ __('All categories') }}</option>
                                @foreach ($categories as $categoryGroup)
                                    <option value="" disabled>{{ $categoryGroup['title'] }}</option>
                                    @foreach ($categoryGroup['children'] as $category)
                                        <option value="{{ $category['category_id'] }}" @selected((string) $filters['category_id'] === (string) $category['category_id'])>
                                            {{ '--- '. $category['title'] }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        <div class="min-w-0 lg:w-52">
                            <label for="condition_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Condition') }}
                            </label>
                            <select
                                id="condition_id"
                                name="condition_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            >
                                <option value="">{{ __('All conditions') }}</option>
                                @foreach ($conditions as $condition)
                                    <option value="{{ $condition->condition_id }}" @selected((string) $filters['condition_id'] === (string) $condition->condition_id)>
                                        {{ $condition->condition_title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-2 whitespace-nowrap lg:w-auto lg:shrink-0">
                            <x-primary-button type="submit">
                                {{ __('Filter') }}
                            </x-primary-button>
                            <a
                                href="{{ route('listings.index', ['status' => $status]) }}"
                                class="inline-flex items-center justify-center rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                            >
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                </form>

                @php
                    $activeFilters = array_filter($filters, fn ($value) => filled($value));
                    $sortUrl = fn (string $column) => route('listings.index', array_merge(
                        ['status' => $status],
                        $activeFilters,
                        [
                            'sort' => $column,
                            'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
                        ],
                    ));
                    $sortLabel = fn (string $column) => $sort === $column ? ($direction === 'asc' ? ' (asc)' : ' (desc)') : '';
                    $sortClass = 'inline-flex items-center text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200';
                @endphp

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('listing_id') }}" class="{{ $sortClass }}">Listing ID{{ $sortLabel('listing_id') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('customer') }}" class="{{ $sortClass }}">Customer{{ $sortLabel('customer') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('products') }}" class="{{ $sortClass }}">Products{{ $sortLabel('products') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('category') }}" class="{{ $sortClass }}">Category{{ $sortLabel('category') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('condition') }}" class="{{ $sortClass }}">Condition{{ $sortLabel('condition') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('created_at') }}" class="{{ $sortClass }}">Created{{ $sortLabel('created_at') }}</a>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <a href="{{ $sortUrl('status') }}" class="{{ $sortClass }}">Status{{ $sortLabel('status') }}</a>
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse ($listings as $listing)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        #{{ $listing->listing_id }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $listing->customer?->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $listing->products->pluck('product_name')->join(', ') ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $listing->products->map(fn ($product) => $product->category?->category_title)->filter()->unique()->join(', ') ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $listing->products->map(fn ($product) => $product->productCondition?->condition_title)->filter()->unique()->join(', ') ?: '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $listing->created_at?->format('M j, Y g:i A') ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-medium {{
                                            match ((int) $listing->status) {
                                                1 => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200',
                                                2 => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200',
                                                3 => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200',
                                                4 => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200',
                                                default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-200',
                                            }
                                        }}">
                                            {{
                                                match ((int) $listing->status) {
                                                    1 => __('Live'),
                                                    2 => __('Rejected'),
                                                    3 => __('Requested'),
                                                    4 => __('Accepted'),
                                                    default => __('Pending'),
                                                }
                                            }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <div class="flex justify-end gap-3">
                                            <a
                                                href="{{ route('listings.show', $listing) }}"
                                                class="font-medium text-gray-700 hover:text-gray-950 dark:text-gray-300 dark:hover:text-white"
                                            >
                                                {{ __('View') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ $statusEmptyMessages[$status] }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($listings->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        {{ $listings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
