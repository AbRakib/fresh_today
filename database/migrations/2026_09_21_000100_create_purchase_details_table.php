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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_detail_number')->unique();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('sell_price', 15, 2)->default(0);
            $table->unsignedInteger('purchase_qty')->default(0);
            $table->unsignedInteger('sell_qty')->default(0);
            $table->unsignedInteger('available_qty')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->date('expire_date')->nullable();
            $table->tinyInteger('receive_status')->default(0)->comment('0=Not received, 1=Received, 2=Partially received');
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
        Schema::dropIfExists('purchase_details');
    }
};
