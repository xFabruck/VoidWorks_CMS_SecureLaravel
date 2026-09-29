<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_access_seo_settings(): void
    {
        $this->get(route('admin.seo.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.seo.update'), [])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_update_global_seo_settings(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.seo.edit'))->assertOk()
            ->assertSee('Configuración SEO')
            ->assertSee('robots_index', false);

        $this->put(route('admin.seo.update'), $this->settingsData())
            ->assertRedirect(route('admin.seo.edit'))->assertSessionHas('status');

        $this->assertDatabaseHas('seo_settings', [
            'singleton_key' => 'main',
            'site_title' => 'Voidworks Studio',
            'default_meta_description' => 'Estudio independiente de videojuegos y experiencias digitales.',
            'default_og_image' => 'https://cdn.example.test/images/social-card.webp',
            'robots_index' => true,
            'robots_follow' => false,
        ]);
        $this->assertSame(1, SeoSetting::query()->count());
    }

    public function test_image_url_requires_public_https_and_allowed_raster_extension(): void
    {
        $this->actingAs(User::factory()->create());

        foreach ([
            'http://cdn.example.test/image.jpg',
            'https://cdn.example.test/image.svg',
            'https://user@cdn.example.test/image.png',
            'javascript:alert(1)',
        ] as $image) {
            $this->from(route('admin.seo.edit'))
                ->put(route('admin.seo.update'), $this->settingsData(['default_og_image' => $image]))
                ->assertRedirect(route('admin.seo.edit'))->assertSessionHasErrors('default_og_image');
        }

        $this->assertDatabaseCount('seo_settings', 0);
    }

    public function test_home_uses_global_fallbacks_and_emits_one_of_each_metadata_tag(): void
    {
        config([
            'seo.site_title' => 'Sitio de prueba',
            'seo.default_meta_description' => 'Descripción general de prueba para los metadatos.',
            'seo.default_og_image' => 'https://cdn.example.test/default.jpg',
            'seo.robots_index' => false,
            'seo.robots_follow' => true,
        ]);

        $response = $this->get(route('home'))->assertOk()
            ->assertSee('<title>Inicio | Sitio de prueba</title>', false)
            ->assertSee('<meta name="description" content="Descripción general de prueba para los metadatos.">', false)
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee('<meta property="og:image" content="https://cdn.example.test/default.jpg">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<link rel="canonical" href="'.config('app.url').'/">', false);

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, '<meta name="description"'));
        $this->assertSame(1, substr_count($html, '<link rel="canonical"'));
        $this->assertSame(1, substr_count($html, 'property="og:title"'));
        $this->assertSame(1, substr_count($html, 'name="twitter:card"'));
    }

    public function test_individual_post_seo_overrides_global_title_and_description(): void
    {
        config([
            'seo.site_title' => 'Nombre global',
            'seo.default_meta_description' => 'Descripción general del CMS.',
            'seo.default_og_image' => 'https://cdn.example.test/default.png',
        ]);
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Noticias', 'slug' => 'noticias', 'description' => null, 'is_active' => true,
        ]);
        $post = new Post;
        $post->title = 'Título editorial';
        $post->slug = 'titulo-editorial';
        $post->excerpt = 'Extracto editorial';
        $post->content = 'Contenido de la publicación.';
        $post->category_id = $category->id;
        $post->author_id = $user->id;
        $post->status = 'published';
        $post->published_at = now()->subMinute();
        $post->seo_title = 'Título SEO individual';
        $post->meta_description = 'Descripción SEO específica de esta publicación.';
        $post->save();

        $this->get(route('news.show', $post->slug))->assertOk()
            ->assertSee('<title>Título SEO individual</title>', false)
            ->assertSee('<meta name="description" content="Descripción SEO específica de esta publicación.">', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertDontSee('<title>Título editorial | Nombre global</title>', false);
    }

    public function test_individual_service_seo_overrides_global_title_and_description(): void
    {
        config([
            'seo.site_title' => 'Nombre global',
            'seo.default_meta_description' => 'Descripción general del CMS.',
        ]);
        $service = Service::query()->create([
            'name' => 'Arte de videojuegos',
            'slug' => 'arte-videojuegos',
            'short_description' => 'Servicios de arte para videojuegos.',
            'description' => 'Descripción detallada del servicio.',
            'icon' => null,
            'image' => null,
            'position' => 0,
            'is_active' => true,
            'seo_title' => 'SEO de arte de videojuegos',
            'meta_description' => 'Metadatos específicos del servicio de arte.',
        ]);

        $this->get(route('services.show', $service->slug))->assertOk()
            ->assertSee('<title>SEO de arte de videojuegos</title>', false)
            ->assertSee('<meta name="description" content="Metadatos específicos del servicio de arte.">', false);
    }

    public function test_individual_metadata_falls_back_to_global_values_when_fields_are_empty(): void
    {
        config([
            'seo.site_title' => 'Nombre global',
            'seo.default_meta_description' => 'Descripción general del CMS.',
            'seo.default_og_image' => null,
        ]);
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Noticias', 'slug' => 'noticias', 'description' => null, 'is_active' => true,
        ]);
        $post = new Post;
        $post->title = 'Contenido sin SEO propio';
        $post->slug = 'sin-seo-propio';
        $post->excerpt = 'No se debe priorizar el extracto sobre la descripción SEO global.';
        $post->content = 'Contenido de prueba.';
        $post->category_id = $category->id;
        $post->author_id = $user->id;
        $post->status = 'published';
        $post->published_at = now()->subMinute();
        $post->seo_title = null;
        $post->meta_description = null;
        $post->save();

        $this->get(route('news.show', $post->slug))->assertOk()
            ->assertSee('<title>Contenido sin SEO propio | Nombre global</title>', false)
            ->assertSee('<meta name="description" content="Descripción general del CMS.">', false)
            ->assertSee('<meta name="twitter:card" content="summary">', false)
            ->assertDontSee('og:image');
    }

    /** @return array<string, mixed> */
    private function settingsData(array $overrides = []): array
    {
        return array_replace([
            'site_title' => 'Voidworks Studio',
            'default_meta_description' => 'Estudio independiente de videojuegos y experiencias digitales.',
            'default_og_image' => 'https://cdn.example.test/images/social-card.webp',
            'robots_index' => '1',
            'robots_follow' => '0',
        ], $overrides);
    }
}
