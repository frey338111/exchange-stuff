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
- Customer account views for listings and claim requests
- Dashboard management for categories, product conditions, CMS pages, customers, and listings
- Listing approval and rejection flow
- Claim request approval flow with queued email notifications

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
- `getMyClaimRequests`
- `registerCustomer`
- `loginCustomer`
- `logoutCustomer`
- `createList`
- `claimItem`
- `approveClaimRequest`

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
php artisan config:clear
php artisan route:list
php artisan lighthouse:print-schema
```
