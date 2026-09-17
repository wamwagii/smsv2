<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN — recreate or use a workaround
        // For MySQL:
        if (DB::connection()->getDriverName() === 'mysql') {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropColumn('class_teacher_id');
            });

            Schema::table('classes', function (Blueprint $table) {
                $table->foreignId('class_teacher_id')
                    ->nullable()
                    ->after('current_enrollment')
                    ->constrained('staff')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Reverse if needed
    }
};