<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('provider', 20);
            $table->string('video_url', 2048);
            $table->string('video_id', 32);
            $table->string('thumbnail', 2048)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->index(['is_active', 'position']);
            $table->index(['provider', 'video_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
