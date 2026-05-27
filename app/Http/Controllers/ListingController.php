<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ProductCondition;
use App\Services\TopNavService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function __construct(private readonly TopNavService $topNavService)
    {
    }

    public function index(Request $request): View
    {
        $status = $this->statusName($request->query('status', 'pending'));
        $statusValue = $this->statusValue($status);
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';
        $productName = $request->query('product_name');
        $categoryId = $request->query('category_id');
        $conditionId = $request->query('condition_id');

        $listings = Listing::query()
            ->with(['customer', 'products.category', 'products.productCondition'])
            ->where('status', $statusValue)
            ->when($productName, function ($query, string $productName) {
                $query->whereHas('products', function ($query) use ($productName) {
                    $query->where('product_name', 'like', "%{$productName}%");
                });
            })
            ->when($categoryId, function ($query, string $categoryId) {
                $query->whereHas('products', function ($query) use ($categoryId) {
                    $query->where('category_id', $categoryId);
                });
            })
            ->when($conditionId, function ($query, string $conditionId) {
                $query->whereHas('products', function ($query) use ($conditionId) {
                    $query->where('condition', $conditionId);
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $listings = $this->paginateListings(
            $this->sortListings($listings, $sort, $direction),
            $request,
        );

        return view('listings.index', [
            'listings' => $listings,
            'status' => $status,
            'categories' => $this->topNavService->topNav(),
            'conditions' => ProductCondition::query()->orderBy('condition_title')->get(),
            'filters' => [
                'product_name' => $productName,
                'category_id' => $categoryId,
                'condition_id' => $conditionId,
            ],
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    private function sortListings($listings, string $sort, string $direction)
    {
        $sortValue = match ($sort) {
            'listing_id' => fn (Listing $listing) => $listing->listing_id,
            'customer' => fn (Listing $listing) => $listing->customer?->name ?? '',
            'products' => fn (Listing $listing) => $listing->products->pluck('product_name')->join(', '),
            'category' => fn (Listing $listing) => $listing->products
                ->map(fn ($product) => $product->category?->category_title)
                ->filter()
                ->unique()
                ->join(', '),
            'condition' => fn (Listing $listing) => $listing->products
                ->map(fn ($product) => $product->productCondition?->condition_title)
                ->filter()
                ->unique()
                ->join(', '),
            'status' => fn (Listing $listing) => $listing->status,
            default => fn (Listing $listing) => $listing->created_at?->timestamp ?? 0,
        };

        return $direction === 'asc'
            ? $listings->sortBy($sortValue)->values()
            : $listings->sortByDesc($sortValue)->values();
    }

    private function paginateListings($listings, Request $request): LengthAwarePaginator
    {
        $perPage = 20;
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $listings->forPage($page, $perPage)->values(),
            $listings->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );
    }

    public function approve(Listing $listing): RedirectResponse
    {
        $listing->update([
            'status' => Listing::STATUS_LIVE,
        ]);

        return redirect()
            ->route('listings.show', $listing)
            ->with('status', 'Listing approved.');
    }

    public function reject(Listing $listing): RedirectResponse
    {
        $listing->update([
            'status' => Listing::STATUS_REJECTED,
        ]);

        return redirect()
            ->route('listings.show', $listing)
            ->with('status', 'Listing rejected.');
    }

    public function show(Listing $listing): View
    {
        $listing->load([
            'customer',
            'products.category',
            'products.productCondition',
            'products.productGalleries',
            'claimRequests.customer',
            'claimRequests.product',
        ]);

        return view('listings.show', [
            'listing' => $listing,
            'galleryImages' => fn ($product) => $product->productGalleries
                ->sortBy(fn ($image) => match ($image->image_type) {
                    'main' => 0,
                    'other' => 1,
                    'thumbnail' => 2,
                    default => 3,
                })
                ->values()
                ->map(fn ($image) => [
                    'url' => Storage::disk('public')->url($image->image_path),
                    'type' => $image->image_type,
                ])
                ->all(),
        ]);
    }

    private function statusName(?string $status): string
    {
        return in_array($status, ['pending', 'live', 'rejected', 'requested', 'accepted'], true) ? $status : 'pending';
    }

    private function statusValue(string $status): int
    {
        return match ($status) {
            'live' => Listing::STATUS_LIVE,
            'rejected' => Listing::STATUS_REJECTED,
            'requested' => Listing::STATUS_REQUESTED,
            'accepted' => Listing::STATUS_ACCEPTED,
            default => Listing::STATUS_PENDING,
        };
    }
}
