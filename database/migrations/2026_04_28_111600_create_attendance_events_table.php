<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('manual_attendance_request_id')->nullable()->constrained('manual_attendance_requests')->nullOnDelete();
            $table->string('event_type', 50);
            $table->string('event_direction', 10)->nullable();
            $table->dateTime('occurred_at');
            $table->string('source', 40)->default('live_capture');
            $table->string('verification_method', 30)->nullable();
            $table->string('snapshot_path', 500)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'occurred_at']);
            $table->index(['attendance_id', 'event_type']);
            $table->index(['source', 'verification_method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_events');
    }
};
