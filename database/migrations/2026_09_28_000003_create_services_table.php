<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->string('short_description', 300);
            $table->longText('description');
            $table->string('icon', 64)->nullable();
            $table->string('image')->nullable();
            $table->string('button_text', 100)->nullable();
            $table->string('button_url', 2048)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(false)->index();
            $table->string('seo_title', 180)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['is_active', 'position'], 'services_public_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
