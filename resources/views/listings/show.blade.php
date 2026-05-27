<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
                    {{ __('Listing') }} #{{ $listing->listing_id }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ $listing->products->pluck('product_name')->join(', ') ?: __('Listing detail') }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ((int) $listing->status !== 1)
                    <form method="POST" action="{{ route('listings.approve', $listing) }}">
                        @csrf
                        <x-primary-button type="submit">
                            {{ __('Approve') }}
                        </x-primary-button>
                    </form>
                @endif

                @if ((int) $listing->status !== 2)
                    <form method="POST" action="{{ route('listings.reject', $listing) }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700"
                        >
                            {{ __('Reject') }}
                        </button>
                    </form>
                @endif

                <a
                    href="{{ route('listings.index', ['status' => match ((int) $listing->status) { 1 => 'live', 2 => 'rejected', 3 => 'requested', 4 => 'accepted', default => 'pending' }]) }}"
                    class="inline-flex items-center justify-center rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                >
                    {{ __('Back to listings') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/40 dark:text-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <section class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="grid gap-6 text-sm md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="font-medium text-gray-500 dark:text-gray-400">{{ __('Customer') }}</p>
                        <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $listing->customer?->name ?? '-' }}</p>
                        <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $listing->customer?->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</p>
                        <p class="mt-1">
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
                        </p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-500 dark:text-gray-400">{{ __('Created') }}</p>
                        <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $listing->created_at?->format('M j, Y g:i A') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="font-medium text-gray-500 dark:text-gray-400">{{ __('Updated') }}</p>
                        <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $listing->updated_at?->format('M j, Y g:i A') ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Listing notes') }}</h3>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700 dark:text-gray-300">
                        {{ $listing->notes ?: __('No notes provided.') }}
                    </p>
                </div>
            </section>

            @foreach ($listing->products as $product)
                @php
                    $images = $galleryImages($product);
                    $carouselImages = count($images) > 0
                        ? $images
                        : [['url' => asset('images/product-placeholder.svg'), 'type' => 'placeholder']];
                @endphp

                <section
                    class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800"
                    x-data="{ activeImage: 0, images: @js($carouselImages) }"
                >
                    <div class="grid gap-8 lg:grid-cols-[minmax(0,26rem)_minmax(0,1fr)]">
                        <div>
                            <div class="overflow-hidden rounded-md border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-900">
                                <img
                                    x-bind:src="images[activeImage].url"
                                    alt="{{ $product->product_name }}"
                                    class="aspect-[4/3] w-full object-cover"
                                >
                            </div>

                            <div x-show="images.length > 1" class="mt-3 flex items-center justify-between gap-3">
                                <button
                                    type="button"
                                    class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                                    x-on:click="activeImage = activeImage === 0 ? images.length - 1 : activeImage - 1"
                                >
                                    {{ __('Previous') }}
                                </button>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    <span x-text="activeImage + 1"></span> / <span x-text="images.length"></span>
                                </p>
                                <button
                                    type="button"
                                    class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                                    x-on:click="activeImage = activeImage === images.length - 1 ? 0 : activeImage + 1"
                                >
                                    {{ __('Next') }}
                                </button>
                            </div>

                            <div x-show="images.length > 1" class="mt-4 flex gap-2 overflow-x-auto pb-1">
                                <template x-for="(image, index) in images" x-bind:key="`${image.url}-${index}`">
                                    <button
                                        type="button"
                                        class="rounded-md border p-1"
                                        x-bind:class="activeImage === index ? 'border-gray-900 dark:border-gray-100' : 'border-gray-200 hover:border-gray-400 dark:border-gray-700 dark:hover:border-gray-500'"
                                        x-on:click="activeImage = index"
                                    >
                                        <img
                                            x-bind:src="image.url"
                                            alt="{{ $product->product_name }}"
                                            class="h-16 w-16 rounded object-cover"
                                        >
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $product->category?->category_title ?? __('Product') }}</p>
                            <h3 class="mt-2 text-2xl font-semibold text-gray-950 dark:text-gray-100">
                                {{ $product->product_name }}
                            </h3>

                            <dl class="mt-6 space-y-3 text-sm">
                                <div class="flex gap-2">
                                    <dt class="shrink-0 font-medium text-gray-600 dark:text-gray-400">{{ __('Reference No:') }}</dt>
                                    <dd class="font-bold text-gray-950 dark:text-gray-100">{{ $product->sku ?? '-' }}</dd>
                                </div>
                                <div class="flex gap-2">
                                    <dt class="shrink-0 font-medium text-gray-600 dark:text-gray-400">{{ __('Condition:') }}</dt>
                                    <dd class="font-bold text-gray-950 dark:text-gray-100">{{ $product->productCondition?->condition_title ?? '-' }}</dd>
                                </div>
                                <div class="flex gap-2">
                                    <dt class="shrink-0 font-medium text-gray-600 dark:text-gray-400">{{ __('Status:') }}</dt>
                                    <dd class="font-bold text-gray-950 dark:text-gray-100">{{ $product->status ? __('Enabled') : __('Disabled') }}</dd>
                                </div>
                            </dl>

                            <div class="mt-8">
                                <h4 class="text-base font-semibold text-gray-950 dark:text-gray-100">{{ __('About this item') }}</h4>
                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700 dark:text-gray-300">
                                    {{ $product->description ?: __('No product details provided.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            @endforeach

            <section class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Claim requests') }}</h3>

                @if ($listing->claimRequests->isNotEmpty())
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="py-3 pe-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Request') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Customer') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Product') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Pickup') }}</th>
                                    <th class="py-3 ps-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($listing->claimRequests as $claimRequest)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pe-4 text-sm font-medium text-gray-900 dark:text-gray-100">#{{ $claimRequest->request_id }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $claimRequest->customer?->name ?? '-' }}
                                            <span class="block text-xs text-gray-500">{{ $claimRequest->customer?->email ?? '' }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $claimRequest->product?->product_name ?? '-' }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $claimRequest->pickup_date?->format('M j, Y g:i A') ?? '-' }}
                                            @if ($claimRequest->timeslot)
                                                <span class="block text-xs text-gray-500">{{ $claimRequest->timeslot }}</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap py-4 ps-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{
                                                match ((int) $claimRequest->status) {
                                                    1 => __('Accepted'),
                                                    2 => __('Rejected'),
                                                    default => __('Pending'),
                                                }
                                            }}
                                        </td>
                                    </tr>
                                    @if ($claimRequest->notes)
                                        <tr>
                                            <td colspan="5" class="pb-4 text-sm text-gray-600 dark:text-gray-300">
                                                <span class="font-medium">{{ __('Notes:') }}</span> {{ $claimRequest->notes }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ __('No claim requests yet.') }}</p>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
