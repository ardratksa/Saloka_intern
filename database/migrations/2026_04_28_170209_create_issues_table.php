<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')
                  ->nullable()
                  ->constrained('checklists')
                  ->nullOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('location_id')
                  ->constrained('location_names')
                  ->cascadeOnDelete();
            $table->date('date');
            $table->string('type', 100);
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'in_progress', 'resolved'])
                  ->default('open');
            $table->boolean('wa_sent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};