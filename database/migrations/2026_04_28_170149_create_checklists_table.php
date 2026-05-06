<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('job_id')
                  ->constrained('master_jobs')
                  ->cascadeOnDelete();
            $table->foreignId('periode_id')
                  ->constrained('periods')
                  ->cascadeOnDelete();
            $table->foreignId('tipe_id')
                  ->constrained('location_types')
                  ->cascadeOnDelete();
            $table->foreignId('location_id')
                  ->constrained('location_names')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->enum('status', ['pending', 'done', 'issue'])
                  ->default('pending');
            $table->text('note')->nullable();
            $table->string('pic', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklists');
    }
};