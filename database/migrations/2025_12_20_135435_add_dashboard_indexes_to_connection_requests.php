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
        Schema::table('connection_requests', function (Blueprint $table) {
            $table->index(['teacher_id', 'status'], 'cr_teacher_status_idx');
            $table->index(['student_id', 'status'], 'cr_student_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('connection_requests', function (Blueprint $table) {
            $table->dropIndex('cr_teacher_status_idx');
            $table->dropIndex('cr_student_status_idx');
        });
    }
};
