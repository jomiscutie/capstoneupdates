<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('coordinator_assignments')) {
            return;
        }

        Schema::create('coordinator_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coordinator_id')->constrained('coordinators')->cascadeOnDelete();
            $table->string('course', 100);
            $table->string('semester', 20);
            $table->string('section', 10);
            $table->timestamps();

            $table->unique(['coordinator_id', 'course', 'semester', 'section'], 'coordinator_assignments_unique_scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coordinator_assignments');
    }
};
