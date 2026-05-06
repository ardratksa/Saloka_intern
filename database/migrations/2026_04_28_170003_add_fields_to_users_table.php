<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'staff'])
                  ->default('staff')->after('email');
            $table->string('wa_number', 20)
                  ->nullable()->after('role');
            $table->string('photo_path')
                  ->nullable()->after('wa_number');
            $table->boolean('is_leader')
                  ->default(false)->after('photo_path');
            $table->boolean('is_active')
                  ->default(true)->after('is_leader');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'wa_number', 'photo_path',
                'is_leader', 'is_active',
            ]);
        });
    }
};