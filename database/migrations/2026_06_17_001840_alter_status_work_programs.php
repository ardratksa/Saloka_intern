<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE work_programs
            MODIFY status ENUM(
                'pending',
                'progress',
                'done',
                'late'
            )
            DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE work_programs
            MODIFY status ENUM(
                'pending',
                'done',
                'late'
            )
            DEFAULT 'pending'
        ");
    }
};