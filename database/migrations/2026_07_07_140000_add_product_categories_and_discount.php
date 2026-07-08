<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_category')) {
            Schema::create('product_category', function (Blueprint $table) {
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_category_id')->constrained()->cascadeOnDelete();
                $table->primary(['product_id', 'product_category_id']);
            });
        }

        // New product fields
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('discount_price')->nullable()->after('price');
            $table->dateTime('discount_end_at')->nullable()->after('discount_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['discount_price', 'discount_end_at']);
        });
        Schema::dropIfExists('product_category');
        Schema::dropIfExists('product_categories');
    }
};
