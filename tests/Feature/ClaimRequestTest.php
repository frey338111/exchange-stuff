<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClaimRequest;
use App\Models\Customer;
use App\Models\Listing;
use App\Models\ListingItem;
use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_claim_item_marks_listing_as_requested(): void
    {
        [$owner, $requester, $listing, $product] = $this->listingFixture();

        $this
            ->withSession(['customer_id' => $requester->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation ClaimItem($input: ClaimItemInput!) {
                        claimItem(input: $input) {
                            success
                            claim_request {
                                status
                            }
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'input' => [
                        'customer_id' => $requester->customer_id,
                        'listing_id' => $listing->listing_id,
                        'product_id' => $product->product_id,
                        'notes' => 'Please approve.',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.claimItem.success', true)
            ->assertJsonPath('data.claimItem.claim_request.status', ClaimRequest::STATUS_PENDING);

        $this->assertSame(Listing::STATUS_REQUESTED, $listing->fresh()->status);
        $this->assertDatabaseHas('claim_request', [
            'customer_id' => $requester->customer_id,
            'listing_id' => $listing->listing_id,
            'product_id' => $product->product_id,
            'status' => ClaimRequest::STATUS_PENDING,
        ]);
    }

    public function test_current_customer_can_view_claim_requests_for_their_listings(): void
    {
        [$owner, $requester, $listing, $product] = $this->listingFixture();

        ClaimRequest::create([
            'customer_id' => $requester->customer_id,
            'listing_id' => $listing->listing_id,
            'product_id' => $product->product_id,
            'status' => ClaimRequest::STATUS_PENDING,
        ]);

        $this
            ->withSession(['customer_id' => $owner->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    {
                        getMyClaimRequests {
                            listing_id
                            status
                            product {
                                product_name
                            }
                        }
                    }
                GRAPHQL,
            ])
            ->assertOk()
            ->assertJsonPath('data.getMyClaimRequests.0.listing_id', (string) $listing->listing_id)
            ->assertJsonPath('data.getMyClaimRequests.0.status', ClaimRequest::STATUS_PENDING)
            ->assertJsonPath('data.getMyClaimRequests.0.product.product_name', 'Desk');

        $this
            ->withSession(['customer_id' => $requester->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    {
                        getMyClaimRequests {
                            request_id
                        }
                    }
                GRAPHQL,
            ])
            ->assertOk()
            ->assertJsonCount(0, 'data.getMyClaimRequests');
    }

    public function test_listing_owner_can_approve_pending_claim_request(): void
    {
        [$owner, $requester, $listing, $product] = $this->listingFixture();
        $otherRequester = Customer::create([
            'name' => 'Other Requester',
            'email' => 'other-requester@example.com',
            'status' => true,
        ]);

        $claimRequest = ClaimRequest::create([
            'customer_id' => $requester->customer_id,
            'listing_id' => $listing->listing_id,
            'product_id' => $product->product_id,
            'status' => ClaimRequest::STATUS_PENDING,
        ]);
        $otherClaimRequest = ClaimRequest::create([
            'customer_id' => $otherRequester->customer_id,
            'listing_id' => $listing->listing_id,
            'product_id' => $product->product_id,
            'status' => ClaimRequest::STATUS_PENDING,
        ]);

        $this
            ->withSession(['customer_id' => $owner->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation ApproveClaimRequest($request_id: Int!) {
                        approveClaimRequest(request_id: $request_id) {
                            success
                            claim_request {
                                status
                            }
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'request_id' => $claimRequest->request_id,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.approveClaimRequest.success', true)
            ->assertJsonPath('data.approveClaimRequest.claim_request.status', ClaimRequest::STATUS_ACCEPTED);

        $this->assertSame(ClaimRequest::STATUS_ACCEPTED, $claimRequest->fresh()->status);
        $this->assertSame(Listing::STATUS_ACCEPTED, $listing->fresh()->status);
        $this->assertSame(ClaimRequest::STATUS_REJECTED, $otherClaimRequest->fresh()->status);
    }

    private function listingFixture(): array
    {
        $owner = Customer::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'status' => true,
        ]);
        $requester = Customer::create([
            'name' => 'Requester',
            'email' => 'requester@example.com',
            'status' => true,
        ]);
        $category = Category::create([
            'category_title' => 'Furniture',
            'url_key' => 'furniture',
            'meta_tag' => 'Furniture',
            'status' => true,
        ]);
        $condition = ProductCondition::create([
            'condition_title' => 'Good',
            'description' => 'Good condition',
            'value_adjustment' => 1,
        ]);
        $product = Product::create([
            'product_name' => 'Desk',
            'url_key' => 'desk',
            'sku' => 'DESK-1',
            'category_id' => $category->category_id,
            'condition' => $condition->condition_id,
            'status' => true,
        ]);
        $listing = Listing::create([
            'customer_id' => $owner->customer_id,
            'status' => Listing::STATUS_LIVE,
        ]);

        ListingItem::create([
            'listing_id' => $listing->listing_id,
            'product_id' => $product->product_id,
        ]);

        return [$owner, $requester, $listing, $product];
    }
}
