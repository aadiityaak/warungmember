<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'role') && DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'member', 'kasir') NOT NULL DEFAULT 'member'");
        }
    }

    public function down(): void
    {
        // no-op — keeping the correct column definition
    }
};
