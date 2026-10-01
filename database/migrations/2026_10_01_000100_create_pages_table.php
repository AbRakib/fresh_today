<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('eyebrow')->nullable();
            $table->text('intro')->nullable();
            $table->json('sections');
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->index(['slug', 'deleted', 'status']);
        });

        DB::table('pages')->insert($this->defaultPages());
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }

    private function defaultPages(): array
    {
        $now = now();

        return [
            [
                'title' => 'FAQ',
                'slug' => 'faq',
                'eyebrow' => 'Common Questions',
                'intro' => 'Answers to the questions customers ask most often before and after ordering from Fresh Today.',
                'sections' => json_encode([
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
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Return Policy',
                'slug' => 'return-policy',
                'eyebrow' => 'Freshness Promise',
                'intro' => 'We want every order to reach you fresh, clean, and as expected. Please review the return guidelines below.',
                'sections' => json_encode([
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
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Shipping Policy',
                'slug' => 'shipping-policy',
                'eyebrow' => 'Delivery Information',
                'intro' => 'Fresh Today prepares and delivers orders with care so your fish, seafood, and meat arrive in good condition.',
                'sections' => json_encode([
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
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms-and-conditions',
                'eyebrow' => 'Website Terms',
                'intro' => 'By using Fresh Today, you agree to the basic terms for browsing, ordering, payment, and delivery.',
                'sections' => json_encode([
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
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
    }
};
