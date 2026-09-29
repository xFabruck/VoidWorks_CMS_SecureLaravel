<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('singleton_key', 16)->default('main')->unique();
            $table->string('title', 160);
            $table->string('subtitle', 220)->nullable();
            $table->longText('content');
            $table->string('primary_image')->nullable();
            $table->text('mission');
            $table->text('vision');
            $table->text('values');
            $table->longText('history');
            $table->string('secondary_image')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};
