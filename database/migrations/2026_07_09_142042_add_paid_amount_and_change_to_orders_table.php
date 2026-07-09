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
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'paid_amount')) {
                $table->integer('paid_amount')->nullable()->after('total_amount');
            }
            if (! Schema::hasColumn('orders', 'change')) {
                $table->integer('change')->nullable()->after('paid_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }
            if (Schema::hasColumn('orders', 'change')) {
                $table->dropColumn('change');
            }
        });
    }
};
