<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('invalidation_status', 20)
                ->default('none')
                ->after('invalidation_reason')
                ->index();
            $table->timestamp('invalidation_requested_at')->nullable()->after('invalidation_status');
            $table->unsignedBigInteger('invalidation_reviewed_by')->nullable()->after('invalidation_requested_at');
            $table->timestamp('invalidation_reviewed_at')->nullable()->after('invalidation_reviewed_by');
            $table->text('invalidation_review_note')->nullable()->after('invalidation_reviewed_at');

            $table->foreign('invalidation_reviewed_by')
                ->references('id')
                ->on('admins')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['invalidation_reviewed_by']);
            $table->dropColumn([
                'invalidation_status',
                'invalidation_requested_at',
                'invalidation_reviewed_by',
                'invalidation_reviewed_at',
                'invalidation_review_note',
            ]);
        });
    }
};
