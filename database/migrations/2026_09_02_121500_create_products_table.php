<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->nullable()->unique();
            $table->string('thumbnail')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('unit')->nullable();
            $table->string('weight')->nullable();
            $table->decimal('regular_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->string('badge')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('minimum_order_quantity')->default(1);
            $table->tinyInteger('is_featured')->default(0)->comment('0=No, 1=Yes');
            $table->tinyInteger('status')->default(1)->comment('0=Inactive, 1=Active');
            $table->timestamp('created_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->tinyInteger('deleted')->default(0)->comment('0=No, 1=Yes');
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
