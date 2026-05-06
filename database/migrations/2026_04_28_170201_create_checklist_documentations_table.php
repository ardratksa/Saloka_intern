<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')
                  ->constrained('checklists')
                  ->cascadeOnDelete();
            $table->string('image');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_documentations');
    }
};