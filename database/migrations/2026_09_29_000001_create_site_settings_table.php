<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('singleton_key', 20)->default('main')->unique();
            $table->string('site_name', 180);
            $table->string('logo_path', 500)->nullable();
            $table->string('favicon_path', 500)->nullable();
            $table->string('contact_email', 254)->nullable();
            $table->string('phone', 60)->nullable();
            $table->string('address', 500)->nullable();
            $table->text('description')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('locale', 12)->default('es');
            $table->string('presentation_timezone', 64)->default('America/Bogota');
            $table->timestamps();
        });

        $siteName = DB::table('seo_settings')->where('singleton_key', 'main')->value('site_title');
        $now = now();

        DB::table('site_settings')->insert([
            'singleton_key' => 'main',
            'site_name' => $siteName ?: config('seo.site_title', config('app.name', 'Sitio web')),
            'locale' => 'es',
            'presentation_timezone' => config('app.timezone', 'America/Bogota'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
