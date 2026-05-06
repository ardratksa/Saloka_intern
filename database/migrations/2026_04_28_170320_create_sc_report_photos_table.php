<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sc_report_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sc_report_id')
                  ->constrained('sc_reports')
                  ->cascadeOnDelete();
            $table->enum('phase', ['before', 'progress', 'after']);
            $table->string('photo_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sc_report_photos');
    }
};