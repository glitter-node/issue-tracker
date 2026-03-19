<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->index('updated_at');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index('issue_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropIndex(['updated_at']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['issue_id']);
            $table->dropIndex(['user_id']);
        });
    }
};
