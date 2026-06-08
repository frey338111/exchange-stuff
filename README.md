# Exchange Stuff

Exchange Stuff is a Laravel application for listing reusable items, browsing them through a Vue storefront, and managing item claims from a dashboard.

## Tech Stack

- PHP 8.3+
- Laravel 13
- Laravel Breeze authentication
- Lighthouse GraphQL
- Vue 3
- Vite
- Tailwind CSS
- PHPUnit
- Laravel Sail, optional

## Features

- Customer registration, login, logout, and session-aware storefront navigation
- Category browsing with filters for child category, condition, and date added
- Product detail pages with image galleries and claim request forms
- Customer listing creation with image uploads
- Customer account views for listings, pickup addresses, received requests, and sent requests
- Dashboard management for categories, product conditions, CMS pages, customers, and listings
- Listing approval and rejection flow
- Claim request accept, reject, amend, and requester reply flows with queued email notifications
- Storefront and email branding using `storage/app/public/logo.png`

## Screenshots



### StoreFront

![Storefront home page](<screenshot/StoreFront-Home.png>)

Home page showing the branded storefront, top navigation, and main content area.

![Storefront category page](<screenshot/StoreFront-Category.png>)

Category browsing page with product listing cards and filters.

![Storefront search](<screenshot/StoreFront-Search.png>)

Quick search interface for finding available products from the storefront navigation.

### Account

![Account registration](<screenshot/Account-register.png>)

Customer registration screen for creating a storefront account.

![Account login](<screenshot/Account-Login.png>)

Customer login screen for account access.

![Account my info](<screenshot/Account-my info.png>)

Customer account profile information view.

![Account my listing](<screenshot/Account-my listing.png>)

Customer listing management view showing the customer's created listings.

![Account create new list](<screenshot/Account-Create New List.png>)

Listing creation modal for adding reusable items with product details and images.

![Account my address](<screenshot/Account-My Address.png>)

Pickup address management view for adding, editing, and deleting saved addresses.

![Account received request](<screenshot/Account-Received Request.png>)

Received requests view for sellers to review claim requests on their listings.

![Account sent requests](<screenshot/Account-Send Requests.png>)

Sent requests view for claimants to track claim requests they have made.

### Admin

![Admin dashboard](<screenshot/Admin-Dashboard.png>)

Admin dashboard landing page.

![Admin add category](<screenshot/Admin-add category.png>)

Category creation form in the admin area.

![Admin edit item condition](<screenshot/Admin-Edit item condition.png>)

Product condition editing screen.

![Admin list customer](<screenshot/Admin-List Customer.png>)

Customer listing view for admin management.

![Admin view CMS pages](<screenshot/Admin-view cms pages.png>)

CMS page management list.

![Admin view listing](<screenshot/Admin-view listing.png>)

Admin listing detail and review screen.

![Admin approve list](<screenshot/Admin-approve list.png>)

Listing approval workflow in the admin area.

### Email

![Email request received](<screenshot/Email request received.png>)

Claim request received email notification.

![Email request updated](<screenshot/Email Request updated.png>)

Claim request amended or updated email notification.

![Email request accepted](<screenshot/Email Request accepted.png>)

Claim request accepted email notification.

### Claim Process

![Claim process step 1 view list item](<screenshot/1 view list item.png>)

1. The claimant views an available listing item in the storefront.

![Claim process step 2 fill claim form](<screenshot/2 fill claim form.png>)

2. The claimant fills in the claim form with pickup preferences and notes.

![Claim process step 3 claim request sent](<screenshot/3 claim request sent.png>)

3. The storefront confirms the claim request has been sent.

![Claim process step 4 seller received request](<screenshot/4 seller received request.png>)

4. The seller sees the received request in their account area.

![Claim process step 5 seller respond by request different time](<screenshot/5 seller respond by request different time.png>)

5. The seller responds by requesting a different pickup time.

![Claim process step 6 conversation history updated](<screenshot/6 conversation history updated.png>)

6. The request detail shows the updated conversation history.

![Claim process step 7 claimant propose alternative time](<screenshot/7 claimant propose alternative time.png>)

7. The claimant replies and proposes an alternative pickup time.

![Claim process step 8 seller accept and send address](<screenshot/8 seller accept and send address.png>)

8. The seller accepts the request and sends pickup address information.

![Claim process step 9 message sent](<screenshot/9 message sent.png>)

9. The final message is sent and visible in the claim conversation.

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- SQLite for the default local setup, or MySQL when using Sail

## Setup

Install PHP dependencies:

```bash
composer install
```

Install JavaScript dependencies:

```bash
npm install
```

Create the environment file and application key:

```bash
cp .env.example .env
php artisan key:generate
```

The default `.env.example` uses SQLite. Create the database file if it does not exist:

```bash
touch database/database.sqlite
```

Run migrations and seed the application data:

```bash
php artisan migrate --seed
```

Create the public storage symlink for uploaded images:

```bash
php artisan storage:link
```

The storefront and email header use the logo at:

```text
storage/app/public/logo.png
```

## Development

Run the Laravel server:

```bash
php artisan serve
```

Run Vite in another terminal:

```bash
npm run dev
```

For the queue worker, run:

```bash
php artisan queue:listen --tries=1 --timeout=0
```

Restart the queue worker after changing mail templates or queued jobs:

```bash
php artisan queue:restart
```

The Composer `dev` script starts the server, queue worker, logs, and Vite together:

```bash
composer run dev
```

## Laravel Sail

This project includes a Sail `compose.yaml` with MySQL, Redis, Meilisearch, Mailpit, and Selenium services.

Start Sail:

```bash
./vendor/bin/sail up -d
```

Run setup commands inside Sail:

```bash
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Mailpit is available at `http://localhost:8025` when Sail is running.

## GraphQL

The GraphQL schema lives in `graphql/schema.graphql`.

The API endpoint is:

```text
/graphql
```

Main operations include:

- `topNav`
- `pageBySlug`
- `productConditions`
- `getCategoryProducts`
- `getProduct`
- `getMyListing`
- `getMyListingDetail`
- `getMyClaimRequests`
- `getMySentClaimRequests`
- `getMySentClaimRequestDetail`
- `getMyAddress`
- `registerCustomer`
- `loginCustomer`
- `logoutCustomer`
- `createList`
- `claimItem`
- `processClaimRequest`
- `respondToAmendedClaimRequest`
- `addAddress`
- `deleteAddress`

## Listing And Claim Statuses

Listing statuses:

- `0` Pending
- `1` Live
- `2` Rejected
- `3` Completed

Claim request statuses:

- `0` Pending
- `1` Accepted
- `2` Rejected
- `3` Amended

Creating a claim request does not change the listing status. When a seller accepts a claim request, the accepted request is set to `Accepted`, other pending or amended requests for the same listing are rejected, and the listing is set to `Completed`.

Category browsing, product detail pages, and quick search only expose products attached to live listings.

## Email

Claim request emails are queued. The amend email is also used for requester replies to amended requests and is sent to the other party based on the latest message author.

The email header embeds `storage/app/public/logo.png` inline when rendered by a Laravel Mailable, with a public `/storage/logo.png` fallback. If mail template changes do not appear in test emails, clear compiled views and restart the queue worker:

```bash
php artisan view:clear
php artisan queue:restart
```

## Dashboard

Dashboard routes are available under:

```text
/dashboard
```

The dashboard is protected by Laravel authentication and includes tools for managing:

- Categories
- Product conditions
- CMS pages
- Listings
- Customers
- User profile

## Testing

Run the test suite:

```bash
composer test
```

Or run Artisan directly:

```bash
php artisan test
```

## Build

Create a production frontend build:

```bash
npm run build
```

## Useful Commands

```bash
php artisan migrate:fresh --seed
php artisan queue:listen --tries=1 --timeout=0
php artisan queue:restart
php artisan view:clear
php artisan config:clear
php artisan route:list
php artisan lighthouse:print-schema
```
