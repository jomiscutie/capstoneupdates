<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('coordinators')->nullOnDelete();
            $table->date('attendance_date');
            $table->time('time_in')->nullable();
            $table->time('lunch_break_out')->nullable();
            $table->time('afternoon_time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->text('reason');
            $table->string('status', 20)->default('pending');
            $table->text('coordinator_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'attendance_date'], 'manual_attendance_requests_student_date_unique');
            $table->index(['status', 'attendance_date'], 'manual_attendance_requests_status_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_attendance_requests');
    }
};
