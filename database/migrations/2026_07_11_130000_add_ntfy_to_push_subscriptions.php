<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->string('ntfy_topic')->nullable()->unique()->after('member_id');
            $table->string('ntfy_token')->nullable()->after('ntfy_topic');
            $table->boolean('subscribed')->default(true)->after('ntfy_token');
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['ntfy_topic', 'ntfy_token', 'subscribed']);
        });
    }
};
