<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE issues MODIFY priority ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE issues MODIFY priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium'");
    }
};
