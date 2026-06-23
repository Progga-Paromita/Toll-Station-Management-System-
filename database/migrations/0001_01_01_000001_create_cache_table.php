<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Stub migration — Laravel's cache/jobs tables are not used with Oracle.
 * Kept as a no-op to satisfy Laravel's migration runner without errors.
 */
return new class extends Migration {
    public function up(): void {}
    public function down(): void {}
};
