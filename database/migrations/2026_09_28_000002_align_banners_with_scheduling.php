<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table): void {
            $table->renameColumn('image_path', 'image');
            $table->renameColumn('cta_label', 'button_text');
            $table->renameColumn('cta_url', 'button_url');
            $table->renameColumn('sort_order', 'position');
        });

        Schema::table('banners', function (Blueprint $table): void {
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->index(['is_active', 'starts_at', 'ends_at', 'position'], 'banners_visibility_index');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table): void {
            $table->dropIndex('banners_visibility_index');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['starts_at', 'ends_at']);
        });

        Schema::table('banners', function (Blueprint $table): void {
            $table->renameColumn('image', 'image_path');
            $table->renameColumn('button_text', 'cta_label');
            $table->renameColumn('button_url', 'cta_url');
            $table->renameColumn('position', 'sort_order');
        });
    }
};
