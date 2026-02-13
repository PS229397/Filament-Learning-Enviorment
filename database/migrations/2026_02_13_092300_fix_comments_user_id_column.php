<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('comments', 'user_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        if (Schema::hasColumn('comments', 'App\\Models\\User')) {
            DB::statement('UPDATE `comments` SET `user_id` = `App\\Models\\User` WHERE `user_id` IS NULL');
            DB::statement('ALTER TABLE `comments` DROP COLUMN `App\\Models\\User`');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('comments', 'user_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};
