<?php

namespace App\GraphQL\Mutations;

use App\DTO\ListingCreateData;
use App\DTO\ListingItemCreateData;
use App\DTO\ProductCreateData;
use App\Services\ListingCreateService;
use App\Services\ListingItemCreateService;
use App\Services\ProductCreateService;
use App\Services\ProductGalleryImageService;
use App\Services\ValidationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateList
{
    public function __construct(
        private readonly ListingCreateService $listingCreateService,
        private readonly ProductCreateService $productCreateService,
        private readonly ListingItemCreateService $listingItemCreateService,
        private readonly ProductGalleryImageService $productGalleryImageService,
        private readonly ValidationService $validationService,
    ) {}

    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): array
    {
        $request = request();
        $customerId = $this->validationService->requireCustomerId(
            $request,
            'You must be logged in to create a list.',
        );

        $input = Validator::make($args['input'], [
            'product_name' => ['required', 'string', 'max:255', 'not_regex:/<[^>]*>/'],
            'product_description' => ['nullable', 'string', 'max:5000', 'not_regex:/<[^>]*>/'],
            'category_id' => ['required', 'integer', 'exists:category,category_id'],
            'condition_id' => ['required', 'integer', 'exists:product_condition,condition_id'],
            'notes' => ['nullable', 'string', 'max:5000', 'not_regex:/<[^>]*>/'],
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'main_image_index' => ['required', 'integer', 'min:0'],
        ])->after(function ($validator) use ($args): void {
            $images = $args['input']['images'] ?? [];
            $mainImageIndex = $args['input']['main_image_index'] ?? null;

            if (is_array($images) && is_int($mainImageIndex) && ! array_key_exists($mainImageIndex, $images)) {
                $validator->errors()->add('main_image_index', 'The selected main image is invalid.');
            }
        })->validate();

        try {
            DB::transaction(function () use ($input, $customerId): void {
                $listing = $this->listingCreateService->create(new ListingCreateData(
                    customerId: $customerId,
                    notes: $input['notes'] ?? null,
                ));

                $product = $this->productCreateService->create(new ProductCreateData(
                    name: $input['product_name'],
                    description: $input['product_description'] ?? null,
                    categoryId: (int) $input['category_id'],
                    conditionId: (int) $input['condition_id'],
                ));

                $this->listingItemCreateService->create(new ListingItemCreateData(
                    listingId: $listing->listing_id,
                    productId: $product->product_id,
                ));

                $this->productGalleryImageService->store(
                    productId: $product->product_id,
                    images: $input['images'],
                    mainImageIndex: (int) $input['main_image_index'],
                );
            });

            return [
                'success' => true,
                'message' => 'List created successfully.',
                'error' => null,
            ];
        } catch (Throwable) {
            return [
                'success' => false,
                'message' => 'The list could not be created.',
                'error' => 'The transaction was rolled back. Please check the submitted details and try again.',
            ];
        }
    }
}
