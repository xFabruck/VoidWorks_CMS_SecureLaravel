<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table): void {
            $table->id();
            $table->string('network', 30);
            $table->string('url', 2048);
            $table->string('icon', 30);
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->index(['is_active', 'position']);
            $table->index('network');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
