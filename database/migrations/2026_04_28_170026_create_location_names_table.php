<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_names', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_type_id')
                  ->constrained('location_types')
                  ->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('qr_code')->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_names');
    }
};