@php
    $globalTitle = trim((string) ($seoSettings->site_title ?: config('seo.site_title') ?: config('seo.safe_site_title')));
    $contentSeoTitle = trim((string) $__env->yieldContent('seo_title'));
    $pageTitle = trim((string) $__env->yieldContent('title'));
    $seoTitle = $contentSeoTitle !== ''
        ? $contentSeoTitle
        : ($pageTitle !== '' ? $pageTitle.' | '.$globalTitle : $globalTitle);
    $contentDescription = trim((string) $__env->yieldContent('seo_description'));
    $pageDescription = trim((string) $__env->yieldContent('meta_description'));
    $seoDescription = $contentDescription !== ''
        ? $contentDescription
        : ($pageDescription !== '' ? $pageDescription : trim((string) ($seoSettings->default_meta_description ?: config('seo.default_meta_description') ?: config('seo.safe_meta_description'))));
    $ogImage = $seoSettings->default_og_image ?: config('seo.default_og_image');
    $ogImageScheme = is_string($ogImage) ? parse_url($ogImage, PHP_URL_SCHEME) : null;
    $ogImageHost = is_string($ogImage) ? parse_url($ogImage, PHP_URL_HOST) : null;
    $ogImageExtension = is_string($ogImage) ? strtolower(pathinfo((string) parse_url($ogImage, PHP_URL_PATH), PATHINFO_EXTENSION)) : '';
    $ogImage = $ogImageScheme === 'https'
        && is_string($ogImageHost)
        && $ogImageHost !== ''
        && in_array($ogImageExtension, ['jpg', 'jpeg', 'png', 'webp'], true)
            ? $ogImage
            : null;
    $canonicalOrigin = rtrim((string) config('app.url'), '/');
    $canonicalPath = request()->getPathInfo();
    $canonicalUrl = $canonicalOrigin.(str_starts_with($canonicalPath, '/') ? $canonicalPath : '/'.$canonicalPath);
    $robotsDirective = ($seoSettings->robots_index ? 'index' : 'noindex').','.($seoSettings->robots_follow ? 'follow' : 'nofollow');
    $openGraphType = trim((string) $__env->yieldContent('og_type', 'website')) ?: 'website';
@endphp

<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="{{ $robotsDirective }}">
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta property="og:site_name" content="{{ $globalTitle }}">
<meta property="og:type" content="{{ $openGraphType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $globalTitle }}">
@endif
<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
@if ($ogImage)<meta name="twitter:image" content="{{ $ogImage }}">@endif
