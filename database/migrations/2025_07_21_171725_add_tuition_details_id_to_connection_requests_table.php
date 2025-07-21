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
            $table->foreignId('tuition_details_id')
                ->after('student_id')
                ->nullable()
                ->constrained('tuition_details')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('connection_requests', function (Blueprint $table) {
            $table->dropForeign(['tuition_details_id']);
            $table->dropColumn('tuition_details_id');
        });
    }
};
