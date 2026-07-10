<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('discount_amount')->nullable()->after('total_amount');
            $table->foreignId('member_voucher_id')->nullable()->after('discount_amount')
                ->constrained('member_vouchers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_voucher_id');
            $table->dropColumn('discount_amount');
        });
    }
};
