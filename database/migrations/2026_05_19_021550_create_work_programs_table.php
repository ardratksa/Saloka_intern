<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_programs', function (Blueprint $table) {

            $table->id();

            /*USER*/

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            /*TYPE*/

            $table->foreignId('location_type_id')
                  ->constrained('location_types')
                  ->cascadeOnDelete();

            /*LOCATION FROM API*/

            $table->unsignedBigInteger('area_id')
                  ->nullable();

            $table->string('area_name')
                  ->nullable();

            $table->string('location_name')
                  ->nullable();

            $table->string('sub_location')
                  ->nullable();

            /*JOB*/

            $table->foreignId('job_id')
                  ->constrained('master_jobs')
                  ->cascadeOnDelete();

            /*PLAN*/

            $table->enum('category', [
                'plan',
                'out_plan'
            ]);

            $table->enum('plan', [
                'monthly',
                'weekly'
            ]);

            /*DETAIL*/

            $table->string('how_to_do')
                  ->nullable();

            $table->string('time_range')
                  ->nullable();

            $table->string('pic')
                  ->nullable();

            /*SCHEDULE*/

            $table->integer('month');

            $table->integer('year');

            // contoh: [1,5,12,20]
            $table->json('scheduled_dates');

            /*STATUS*/

            $table->enum('status', [
                'pending',
                'done'
            ])->default('pending');

            $table->boolean('has_evidence')
                  ->default(false);

            $table->timestamp('completed_at')
                  ->nullable();

            $table->string('checker')
                  ->nullable();

            $table->text('remark')
                  ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_programs');
    }
};