<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (! Schema::hasColumn('members', 'last_outlet_id')) {
                $table->foreignId('last_outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'last_outlet_id')) {
                $table->dropForeign(['last_outlet_id']);
                $table->dropColumn('last_outlet_id');
            }
        });
    }
};
