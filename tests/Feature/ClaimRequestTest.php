<?php

namespace Tests\Feature;

use App\Jobs\SendRequestAmendEmail;
use App\Models\Category;
use App\Models\ClaimRequest;
use App\Models\ClaimRequestMessage;
use App\Models\Customer;
use App\Models\Listing;
use App\Models\ListingItem;
use App\Models\PickupAddress;
use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ClaimRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_claim_item_does_not_change_listing_status(): void
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

        $this->assertSame(Listing::STATUS_LIVE, $listing->fresh()->status);
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

        $this
            ->withSession(['customer_id' => $requester->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    {
                        getMySentClaimRequests {
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
            ->assertJsonPath('data.getMySentClaimRequests.0.listing_id', (string) $listing->listing_id)
            ->assertJsonPath('data.getMySentClaimRequests.0.status', ClaimRequest::STATUS_PENDING)
            ->assertJsonPath('data.getMySentClaimRequests.0.product.product_name', 'Desk');
    }

    public function test_current_customer_can_view_their_sent_claim_request_detail(): void
    {
        [$owner, $requester, $listing, $product] = $this->listingFixture();

        $claimRequest = ClaimRequest::create([
            'customer_id' => $requester->customer_id,
            'listing_id' => $listing->listing_id,
            'product_id' => $product->product_id,
            'notes' => 'I can collect this week.',
            'status' => ClaimRequest::STATUS_PENDING,
        ]);
        $otherRequester = Customer::create([
            'name' => 'Other Requester',
            'email' => 'other-sent-detail@example.com',
            'status' => true,
        ]);

        $this
            ->withSession(['customer_id' => $requester->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    query SentRequest($request_id: Int!) {
                        getMySentClaimRequestDetail(request_id: $request_id) {
                            request_id
                            customer_id
                            notes
                            product {
                                product_name
                                category {
                                    category_title
                                }
                                productCondition {
                                    condition_title
                                }
                            }
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'request_id' => $claimRequest->request_id,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.getMySentClaimRequestDetail.request_id', (string) $claimRequest->request_id)
            ->assertJsonPath('data.getMySentClaimRequestDetail.customer_id', (string) $requester->customer_id)
            ->assertJsonPath('data.getMySentClaimRequestDetail.notes', 'I can collect this week.')
            ->assertJsonPath('data.getMySentClaimRequestDetail.product.product_name', 'Desk')
            ->assertJsonPath('data.getMySentClaimRequestDetail.product.category.category_title', 'Furniture')
            ->assertJsonPath('data.getMySentClaimRequestDetail.product.productCondition.condition_title', 'Good');

        $this
            ->withSession(['customer_id' => $otherRequester->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    query SentRequest($request_id: Int!) {
                        getMySentClaimRequestDetail(request_id: $request_id) {
                            request_id
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'request_id' => $claimRequest->request_id,
                ],
            ])
            ->assertJsonPath('errors.0.message', 'Sent request could not be found.');
    }

    public function test_listing_owner_can_process_claim_request_as_accepted(): void
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
        $amendedClaimRequest = ClaimRequest::create([
            'customer_id' => $otherRequester->customer_id,
            'listing_id' => $listing->listing_id,
            'product_id' => $product->product_id,
            'status' => ClaimRequest::STATUS_AMENDED,
        ]);

        $this
            ->withSession(['customer_id' => $owner->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation ProcessClaimRequest($input: ProcessClaimRequestInput!) {
                        processClaimRequest(input: $input) {
                            success
                            claim_request {
                                status
                            }
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'input' => [
                        'request_id' => $claimRequest->request_id,
                        'action' => 'accept',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.processClaimRequest.success', true)
            ->assertJsonPath('data.processClaimRequest.claim_request.status', ClaimRequest::STATUS_ACCEPTED);

        $this->assertSame(ClaimRequest::STATUS_ACCEPTED, $claimRequest->fresh()->status);
        $this->assertSame(Listing::STATUS_COMPLETED, $listing->fresh()->status);
        $this->assertSame(ClaimRequest::STATUS_REJECTED, $otherClaimRequest->fresh()->status);
        $this->assertSame(ClaimRequest::STATUS_REJECTED, $amendedClaimRequest->fresh()->status);
    }

    public function test_listing_owner_can_reject_claim_request_with_message(): void
    {
        [$owner, $requester, $listing, $product] = $this->listingFixture();

        $claimRequest = ClaimRequest::create([
            'customer_id' => $requester->customer_id,
            'listing_id' => $listing->listing_id,
            'product_id' => $product->product_id,
            'status' => ClaimRequest::STATUS_PENDING,
        ]);

        $this
            ->withSession(['customer_id' => $owner->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation ProcessClaimRequest($input: ProcessClaimRequestInput!) {
                        processClaimRequest(input: $input) {
                            success
                            claim_request {
                                status
                            }
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'input' => [
                        'request_id' => $claimRequest->request_id,
                        'action' => 'reject',
                        'message' => 'Sorry, this is no longer available.',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.processClaimRequest.claim_request.status', ClaimRequest::STATUS_REJECTED);

        $this->assertSame(ClaimRequest::STATUS_REJECTED, $claimRequest->fresh()->status);
        $this->assertDatabaseHas('claim_request_message', [
            'request_id' => $claimRequest->request_id,
            'customer_id' => $owner->customer_id,
            'message' => 'Sorry, this is no longer available.',
        ]);
    }

    public function test_listing_owner_can_amend_claim_request_with_address_and_pickup_time(): void
    {
        Queue::fake();

        [$owner, $requester, $listing, $product] = $this->listingFixture();

        $claimRequest = ClaimRequest::create([
            'customer_id' => $requester->customer_id,
            'listing_id' => $listing->listing_id,
            'product_id' => $product->product_id,
            'status' => ClaimRequest::STATUS_PENDING,
        ]);
        $address = PickupAddress::create([
            'customer_id' => $owner->customer_id,
            'address_line_1' => '10 Test Street',
            'city' => 'London',
            'postcode' => 'N1 1AA',
            'country' => 'GB',
        ]);

        $this
            ->withSession(['customer_id' => $owner->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation ProcessClaimRequest($input: ProcessClaimRequestInput!) {
                        processClaimRequest(input: $input) {
                            success
                            claim_request {
                                status
                                pickup_date
                                timeslot
                            }
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'input' => [
                        'request_id' => $claimRequest->request_id,
                        'action' => 'amend',
                        'message' => 'Please use the side gate.',
                        'stored_address_id' => $address->pickup_address_id,
                        'pickup_date' => '2026-07-01',
                        'pickup_slot' => '9am-12pm',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.processClaimRequest.claim_request.status', ClaimRequest::STATUS_AMENDED)
            ->assertJsonPath('data.processClaimRequest.claim_request.timeslot', '9am-12pm');

        $claimRequest->refresh();
        $this->assertSame(ClaimRequest::STATUS_AMENDED, $claimRequest->status);
        $this->assertSame(Listing::STATUS_LIVE, $listing->fresh()->status);
        $this->assertSame('9am-12pm', $claimRequest->timeslot);
        $this->assertSame('2026-07-01 00:00:00', $claimRequest->pickup_date->format('Y-m-d H:i:s'));

        $message = ClaimRequestMessage::query()->where('request_id', $claimRequest->request_id)->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('Please use the side gate.', $message->message);
        $this->assertStringContainsString('Collection address is 10 Test Street, London, N1 1AA, GB', $message->message);
        Queue::assertPushed(SendRequestAmendEmail::class);
    }

    public function test_requester_can_reply_to_amended_claim_request(): void
    {
        Queue::fake();

        [$owner, $requester, $listing, $product] = $this->listingFixture();

        $claimRequest = ClaimRequest::create([
            'customer_id' => $requester->customer_id,
            'listing_id' => $listing->listing_id,
            'product_id' => $product->product_id,
            'status' => ClaimRequest::STATUS_AMENDED,
        ]);

        $this
            ->withSession(['customer_id' => $requester->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation ReplyToAmendedClaimRequest($input: RespondToAmendedClaimRequestInput!) {
                        respondToAmendedClaimRequest(input: $input) {
                            success
                            claim_request {
                                status
                                pickup_date
                                timeslot
                            }
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'input' => [
                        'request_id' => $claimRequest->request_id,
                        'message' => 'That pickup time works for me.',
                        'pickup_date' => '2026-07-02',
                        'pickup_slot' => '3pm-6pm',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.respondToAmendedClaimRequest.success', true)
            ->assertJsonPath('data.respondToAmendedClaimRequest.claim_request.status', ClaimRequest::STATUS_PENDING)
            ->assertJsonPath('data.respondToAmendedClaimRequest.claim_request.timeslot', '3pm-6pm');

        $claimRequest->refresh();
        $this->assertSame(ClaimRequest::STATUS_PENDING, $claimRequest->status);
        $this->assertSame('3pm-6pm', $claimRequest->timeslot);
        $this->assertSame('2026-07-02 00:00:00', $claimRequest->pickup_date->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('claim_request_message', [
            'request_id' => $claimRequest->request_id,
            'customer_id' => $requester->customer_id,
            'message' => 'That pickup time works for me.',
        ]);
        Queue::assertPushed(SendRequestAmendEmail::class);

        $claimRequest->update(['status' => ClaimRequest::STATUS_AMENDED]);

        $this
            ->withSession(['customer_id' => $owner->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation ReplyToAmendedClaimRequest($input: RespondToAmendedClaimRequestInput!) {
                        respondToAmendedClaimRequest(input: $input) {
                            success
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'input' => [
                        'request_id' => $claimRequest->request_id,
                        'message' => 'Owner should not reply here.',
                    ],
                ],
            ])
            ->assertJsonPath('errors.0.message', 'Sent request could not be found.');
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
