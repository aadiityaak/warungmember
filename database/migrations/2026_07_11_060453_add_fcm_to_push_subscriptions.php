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
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('p256dh');
            $table->string('platform', 20)->default('web')->after('fcm_token');

            $table->string('endpoint')->nullable()->change();
            $table->string('auth')->nullable()->change();
            $table->string('p256dh')->nullable()->change();

            $table->index('member_id');
            $table->index('platform');
            $table->dropUnique(['member_id', 'endpoint']);
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['platform']);
            $table->dropIndex(['member_id']);
            $table->unique(['member_id', 'endpoint']);

            $table->string('p256dh')->nullable(false)->change();
            $table->string('auth')->nullable(false)->change();
            $table->string('endpoint')->nullable(false)->change();

            $table->dropColumn(['fcm_token', 'platform']);
        });
    }
};
