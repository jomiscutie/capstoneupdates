<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_term_assignments')) {
            return;
        }

        Schema::create('student_term_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('course', 100);
            $table->string('school_year', 20)->nullable();
            $table->string('term', 30);
            $table->string('section', 10);
            $table->decimal('required_ojt_hours', 8, 2)->default(120);
            $table->string('status', 20)->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_term_assignments');
    }
};
