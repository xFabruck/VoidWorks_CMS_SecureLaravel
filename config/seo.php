<?php

return [
    'site_title' => env('SEO_SITE_TITLE', env('APP_NAME', 'Voidworks Studio')),
    'default_meta_description' => env('SEO_DEFAULT_META_DESCRIPTION', 'Sitio web oficial de Voidworks Studio.'),
    'default_og_image' => env('SEO_DEFAULT_OG_IMAGE'),
    'robots_index' => (bool) env('SEO_ROBOTS_INDEX', true),
    'robots_follow' => (bool) env('SEO_ROBOTS_FOLLOW', true),
    'safe_site_title' => 'Sitio web oficial',
    'safe_meta_description' => 'Información y novedades del sitio web oficial.',
];
