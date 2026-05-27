<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_program_evidences', function (Blueprint $table) {

            $table->id();

            $table->foreignId('work_program_id')
                  ->constrained('work_programs')
                  ->cascadeOnDelete();

            $table->string('before_image')
                  ->nullable();

            $table->string('after_image')
                  ->nullable();

            $table->text('remark')
                  ->nullable();

            $table->date('date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_program_evidences');
    }
};