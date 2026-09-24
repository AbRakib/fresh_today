<?php

use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryChargeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (ProductController $products) {
    return Inertia::render('frontend/Welcome', [
        'frontend_products' => $products->frontendProducts(),
    ]);
})->name('home');
Route::get('/shop', [ProductController::class, 'frontendIndex'])->name('shop');
Route::get('/product/{product:slug}', [ProductController::class, 'frontendShow'])->name('products.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'backend/Dashboard')->name('dashboard');
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::post('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('subcategories', [SubcategoryController::class, 'index'])->name('subcategories.index');
    Route::post('subcategories', [SubcategoryController::class, 'store'])->name('subcategories.store');
    Route::post('subcategories/{subcategory}', [SubcategoryController::class, 'update'])->name('subcategories.update');
    Route::delete('subcategories/{subcategory}', [SubcategoryController::class, 'destroy'])->name('subcategories.destroy');

    Route::get('units', [UnitController::class, 'index'])->name('units.index');
    Route::post('units', [UnitController::class, 'store'])->name('units.store');
    Route::post('units/{unit}', [UnitController::class, 'update'])->name('units.update');
    Route::post('units/{unit}/toggle-default', [UnitController::class, 'toggleDefault'])->name('units.toggle-default');
    Route::delete('units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

    Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::post('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    Route::get('bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts.index');
    Route::post('bank-accounts', [BankAccountController::class, 'store'])->name('bank-accounts.store');
    Route::post('bank-accounts/{bankAccount}', [BankAccountController::class, 'update'])->name('bank-accounts.update');
    Route::post('bank-accounts/{bankAccount}/toggle-default', [BankAccountController::class, 'toggleDefault'])->name('bank-accounts.toggle-default');
    Route::delete('bank-accounts/{bankAccount}', [BankAccountController::class, 'destroy'])->name('bank-accounts.destroy');

    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');

    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::post('products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
    Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
    Route::post('purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
    Route::post('purchases/{purchase}/payment', [PurchaseController::class, 'payment'])->name('purchases.payment');
    Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::post('orders/{order}/advance-status', [OrderController::class, 'advanceStatus'])->name('orders.advance-status');
    Route::post('orders/{order}/payment', [OrderController::class, 'payment'])->name('orders.payment');
    Route::post('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/stock/pdf', [ReportController::class, 'stockPdf'])->name('reports.stock.pdf');
    Route::get('reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('reports/profit-loss/pdf', [ReportController::class, 'profitLossPdf'])->name('reports.profit-loss.pdf');

    Route::get('delivery-charges', [DeliveryChargeController::class, 'index'])->name('delivery-charges.index');
    Route::post('delivery-charges', [DeliveryChargeController::class, 'store'])->name('delivery-charges.store');
    Route::post('delivery-charges/{deliveryCharge}', [DeliveryChargeController::class, 'update'])->name('delivery-charges.update');
    Route::delete('delivery-charges/{deliveryCharge}', [DeliveryChargeController::class, 'destroy'])->name('delivery-charges.destroy');
});

require __DIR__.'/settings.php';
