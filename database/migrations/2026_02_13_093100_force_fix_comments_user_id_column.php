<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schema = DB::getDatabaseName();

        $hasGood = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', $schema)
            ->where('TABLE_NAME', 'comments')
            ->where('COLUMN_NAME', 'user_id')
            ->exists();

        $hasBad = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', $schema)
            ->where('TABLE_NAME', 'comments')
            ->where('COLUMN_NAME', 'App\\Models\\User')
            ->exists();

        if ($hasBad && ! $hasGood) {
            DB::statement('ALTER TABLE `comments` CHANGE COLUMN `App\\Models\\User` `user_id` BIGINT UNSIGNED NULL');
            $hasGood = true;
            $hasBad = false;
        }

        if ($hasBad && $hasGood) {
            DB::statement('UPDATE `comments` SET `user_id` = `App\\Models\\User` WHERE `user_id` IS NULL');
            DB::statement('ALTER TABLE `comments` DROP COLUMN `App\\Models\\User`');
            $hasBad = false;
        }

        if (! $hasGood) {
            DB::statement('ALTER TABLE `comments` ADD COLUMN `user_id` BIGINT UNSIGNED NULL AFTER `id`');
            $hasGood = true;
        }

        $hasFk = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', $schema)
            ->where('TABLE_NAME', 'comments')
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->where('CONSTRAINT_NAME', 'comments_user_id_foreign')
            ->exists();

        if (! $hasFk) {
            DB::statement('ALTER TABLE `comments` ADD CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = DB::getDatabaseName();

        $hasFk = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', $schema)
            ->where('TABLE_NAME', 'comments')
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->where('CONSTRAINT_NAME', 'comments_user_id_foreign')
            ->exists();

        if ($hasFk) {
            DB::statement('ALTER TABLE `comments` DROP FOREIGN KEY `comments_user_id_foreign`');
        }
    }
};
