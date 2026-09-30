<?php

use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryChargeController;
use App\Http\Controllers\FrontendCustomerAuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (ProductController $products) {
    return Inertia::render('frontend/Welcome', [
        'frontend_products' => $products->frontendProducts(),
    ]);
})->name('home');
Route::inertia('/about', 'frontend/About')->name('about');
Route::inertia('/help-center', 'frontend/StaticPage', [
    'page' => [
        'title' => 'Help Center',
        'eyebrow' => 'Customer Support',
        'intro' => 'Find quick answers about ordering, payment, delivery, and getting support from Fresh Today.',
        'sections' => [
            [
                'title' => 'How can we help?',
                'items' => [
                    'Browse the shop, choose your fresh products, and add them to your cart.',
                    'Review your cart before checkout to confirm quantity, weight, price, and delivery details.',
                    'Contact our support team if you need help with an order, payment, delivery address, or product question.',
                ],
            ],
            [
                'title' => 'Order Support',
                'items' => [
                    'Keep your order information ready when contacting support so we can help faster.',
                    'If an item becomes unavailable, our team may contact you to confirm a suitable replacement or adjustment.',
                    'For urgent delivery questions, use the phone number shown in the site header or footer.',
                ],
            ],
        ],
    ],
])->name('help-center');
Route::inertia('/faq', 'frontend/StaticPage', [
    'page' => [
        'title' => 'FAQ',
        'eyebrow' => 'Common Questions',
        'intro' => 'Answers to the questions customers ask most often before and after ordering from Fresh Today.',
        'sections' => [
            [
                'title' => 'Ordering',
                'items' => [
                    'You can order directly from the shop by adding products to your cart and completing checkout.',
                    'Product availability can change based on freshness, supply, and daily stock.',
                    'Prices, weights, and discounts shown on product pages are updated from the current catalog.',
                ],
            ],
            [
                'title' => 'Delivery & Payment',
                'items' => [
                    'Delivery options and charges are confirmed during checkout.',
                    'Please provide a reachable phone number so our team can confirm order details if needed.',
                    'Accepted payment methods may include cash on delivery and the payment options shown on the website.',
                ],
            ],
        ],
    ],
])->name('faq');
Route::inertia('/return-policy', 'frontend/StaticPage', [
    'page' => [
        'title' => 'Return Policy',
        'eyebrow' => 'Freshness Promise',
        'intro' => 'We want every order to reach you fresh, clean, and as expected. Please review the return guidelines below.',
        'sections' => [
            [
                'title' => 'Eligible Issues',
                'items' => [
                    'Contact us as soon as possible if a product arrives damaged, spoiled, missing, or different from your confirmed order.',
                    'Fresh food items should be checked at delivery because return eligibility may depend on product condition and timing.',
                    'Photos or order details may be requested so our team can review the issue quickly.',
                ],
            ],
            [
                'title' => 'Resolution',
                'items' => [
                    'Depending on the situation, we may arrange a replacement, adjustment, refund, or store credit.',
                    'Items that have been used, cooked, stored incorrectly, or reported too late may not qualify for return.',
                    'Final approval is based on the product condition, delivery record, and order details.',
                ],
            ],
        ],
    ],
])->name('return-policy');
Route::inertia('/shipping-policy', 'frontend/StaticPage', [
    'page' => [
        'title' => 'Shipping Policy',
        'eyebrow' => 'Delivery Information',
        'intro' => 'Fresh Today prepares and delivers orders with care so your fish, seafood, and meat arrive in good condition.',
        'sections' => [
            [
                'title' => 'Delivery Areas',
                'items' => [
                    'Available delivery areas are based on our current service coverage and may change over time.',
                    'Delivery charges are calculated during checkout based on the available delivery setup.',
                    'If your address needs clarification, our team may call before dispatching the order.',
                ],
            ],
            [
                'title' => 'Delivery Timing',
                'items' => [
                    'Orders are prepared according to product availability, order volume, and delivery route.',
                    'Please keep your phone reachable around delivery time to avoid delays.',
                    'Weather, traffic, supply conditions, or operational issues may affect delivery timing.',
                ],
            ],
        ],
    ],
])->name('shipping-policy');
Route::inertia('/terms-and-conditions', 'frontend/StaticPage', [
    'page' => [
        'title' => 'Terms & Conditions',
        'eyebrow' => 'Website Terms',
        'intro' => 'By using Fresh Today, you agree to the basic terms for browsing, ordering, payment, and delivery.',
        'sections' => [
            [
                'title' => 'Using the Website',
                'items' => [
                    'Please provide accurate account, contact, delivery, and payment information when placing an order.',
                    'Product images, prices, stock, discounts, and availability may change without prior notice.',
                    'Fresh Today may refuse or cancel orders that contain incorrect information, unavailable items, or suspected misuse.',
                ],
            ],
            [
                'title' => 'Orders & Responsibility',
                'items' => [
                    'Customers are responsible for reviewing order details before confirming checkout.',
                    'Fresh food should be received, checked, and stored properly after delivery.',
                    'Policy pages may be updated when our service, operations, or legal requirements change.',
                ],
            ],
        ],
    ],
])->name('terms-and-conditions');
Route::get('/shop', [ProductController::class, 'frontendIndex'])->name('shop');
Route::get('/product/{product:slug}', [ProductController::class, 'frontendShow'])->name('products.show');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->name('wishlist.store');
Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/customer/login', [FrontendCustomerAuthController::class, 'login'])->name('frontend.customer.login');
Route::post('/customer/register', [FrontendCustomerAuthController::class, 'register'])->name('frontend.customer.register');
Route::post('/customer/logout', [FrontendCustomerAuthController::class, 'logout'])->name('frontend.customer.logout');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'backend/Dashboard')->name('dashboard');
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::post('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::patch('customers/{customer}/password', [CustomerController::class, 'updatePassword'])->name('customers.password.update');
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
    Route::get('orders/{order}/pdf', [OrderController::class, 'pdf'])->name('orders.pdf');
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
