<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Stub migration — Laravel's jobs/queues table is not used with Oracle.
 * Kept as a no-op to satisfy Laravel's migration runner without errors.
 */
return new class extends Migration {
    public function up(): void {}
    public function down(): void {}
};
