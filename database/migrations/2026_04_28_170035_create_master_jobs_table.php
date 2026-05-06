<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_type_id')
                  ->constrained('location_types')
                  ->cascadeOnDelete();
            $table->string('job', 255);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_jobs');
    }
};