<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 180)->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->dateTime('published_at')->nullable();
            $table->string('seo_title', 180)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at'], 'posts_publication_schedule_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
