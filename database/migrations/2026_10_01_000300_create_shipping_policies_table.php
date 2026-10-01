<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_policies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('items');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->index(['deleted', 'status', 'sort_order']);
        });

        $page = DB::table('pages')->where('slug', 'shipping-policy')->where('deleted', 0)->first();

        foreach (json_decode($page?->sections ?? '[]', true, 512, JSON_THROW_ON_ERROR) as $index => $section) {
            DB::table('shipping_policies')->insert([
                'title' => $section['title'],
                'items' => json_encode($section['items'], JSON_THROW_ON_ERROR),
                'sort_order' => $index,
                'status' => $page->status,
                'created_by' => $page->created_by,
                'updated_by' => $page->updated_by,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_policies');
    }
};
