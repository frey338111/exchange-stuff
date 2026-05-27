<?php

use App\DataProviders\DashboardDataProviders\LiveListingsDataProvider;
use App\DataProviders\DashboardDataProviders\PendingListingsDataProvider;
use App\DataProviders\DashboardDataProviders\RegisteredCustomersDataProvider;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductConditionController;
use App\Http\Controllers\ProfileController;
use App\ViewModels\DashboardViewModel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::prefix('dashboard')->middleware(['storefront.customer.redirect', 'auth'])->group(function () {
    Route::get('/', function () {
        return view('dashboard', new DashboardViewModel(
            new PendingListingsDataProvider(),
            new LiveListingsDataProvider(),
            new RegisteredCustomersDataProvider(),
        ));
    })->middleware('verified')->name('dashboard');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');

    Route::get('product-conditions', [ProductConditionController::class, 'index'])->name('product-conditions.index');
    Route::post('product-conditions', [ProductConditionController::class, 'store'])->name('product-conditions.store');
    Route::put('product-conditions/{productCondition}', [ProductConditionController::class, 'update'])->name('product-conditions.update');

    Route::post('pages/upload-image', [PageController::class, 'uploadImage'])->name('dashboard.pages.upload-image');
    Route::resource('pages', PageController::class)->except(['show'])->names('dashboard.pages');

    Route::get('list', [ListingController::class, 'index'])->name('listings.index');
    Route::post('list/{listing}/approve', [ListingController::class, 'approve'])->name('listings.approve');
    Route::post('list/{listing}/reject', [ListingController::class, 'reject'])->name('listings.reject');
    Route::get('list/{listing}', [ListingController::class, 'show'])->name('listings.show');

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('storage/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->where('path', '.*');

Route::get('/{any?}', function () {
    return view('frontend');
})->where('any', '^(?!dashboard(?:/|$)|graphql(?:/|$)).*');
