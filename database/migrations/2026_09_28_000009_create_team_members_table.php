<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('position', 160);
            $table->text('biography')->nullable();
            $table->string('photo')->nullable();
            $table->string('email', 254)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('linkedin_url', 2048)->nullable();
            $table->unsignedSmallInteger('position_order')->default(0);
            $table->boolean('is_active')->default(false);
            $table->boolean('show_biography')->default(false);
            $table->boolean('show_photo')->default(false);
            $table->boolean('show_email')->default(false);
            $table->boolean('show_phone')->default(false);
            $table->boolean('show_linkedin_url')->default(false);
            $table->timestamps();
            $table->index(['is_active', 'position_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
