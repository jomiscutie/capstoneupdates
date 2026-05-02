<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_invalid')->default(false)->after('timeout_verification_snapshot')->index();
            $table->timestamp('invalidated_at')->nullable()->after('is_invalid');
            $table->unsignedBigInteger('invalidated_by')->nullable()->after('invalidated_at');
            $table->text('invalidation_reason')->nullable()->after('invalidated_by');

            $table->foreign('invalidated_by')
                ->references('id')
                ->on('coordinators')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['invalidated_by']);
            $table->dropColumn([
                'is_invalid',
                'invalidated_at',
                'invalidated_by',
                'invalidation_reason',
            ]);
        });
    }
};
