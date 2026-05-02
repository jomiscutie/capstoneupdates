<?php

use App\Models\DynamicOption;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('dynamic_options')) {
            return;
        }

        DynamicOption::query()
            ->where('type', DynamicOption::TYPE_SECTION)
            ->where('is_active', false)
            ->delete();
    }

    public function down(): void
    {
        // Irreversible: removed rows are not restored.
    }
};
