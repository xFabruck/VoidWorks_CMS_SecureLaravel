<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('mailer', 40)->default('smtp');
            $table->string('host', 253)->nullable();
            $table->unsignedSmallInteger('port')->nullable();
            $table->string('encryption', 12)->nullable();
            $table->string('username', 254)->nullable();
            $table->text('password')->nullable();
            $table->string('from_address', 254)->nullable();
            $table->string('from_name', 180)->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('mail_setting_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mail_setting_id')->nullable()->constrained('mail_settings')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_setting_audits');
        Schema::dropIfExists('mail_settings');
    }
};
