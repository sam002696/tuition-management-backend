<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tuition_details', function (Blueprint $table) {
            $table->id();
            $table->enum('tuition_type', ['monthly_based', 'course']);
            $table->string('class_level');
            $table->json('subject_list');
            $table->string('medium');
            $table->string('institute_name');
            $table->string('address_line');
            $table->string('district');
            $table->string('thana');
            $table->text('study_purpose');

            // Monthly based
            $table->tinyInteger('tuition_days_per_week')->nullable();
            $table->tinyInteger('hours_per_day')->nullable();
            $table->json('days_name')->nullable();
            $table->integer('salary_per_month')->nullable();
            $table->string('starting_month')->nullable();

            // Course based
            $table->integer('total_classes_per_course')->nullable();
            $table->float('hours_per_class')->nullable();
            $table->integer('salary_per_subject')->nullable();
            $table->integer('total_course_completion_salary')->nullable();
            $table->string('duration')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuition_details');
    }
};
