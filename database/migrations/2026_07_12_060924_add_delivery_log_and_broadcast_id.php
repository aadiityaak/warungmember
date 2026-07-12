<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->json('delivery_log')->nullable()->after('sent_count');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('broadcast_id')->nullable()->after('member_id')->constrained('broadcasts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['broadcast_id']);
            $table->dropColumn('broadcast_id');
        });

        Schema::table('broadcasts', function (Blueprint $table) {
            $table->dropColumn('delivery_log');
        });
    }
};
