<?php

use App\Http\Controllers\Admin\AboutPageController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SeoSettingsController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetOtpController;
use App\Http\Controllers\Site\AboutPageController as PublicAboutPageController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\NewsController;
use App\Http\Controllers\Site\ServiceController as PublicServiceController;
use App\Http\Controllers\Site\TeamController;
use App\Http\Controllers\Site\TestimonialsController;
use App\Http\Controllers\Site\VideosController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/contacto', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contacto', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/servicios', [PublicServiceController::class, 'index'])->name('services.index');
Route::get('/servicios/{slug}', [PublicServiceController::class, 'show'])->name('services.show');
Route::get('/nosotros', PublicAboutPageController::class)->name('about');
Route::get('/noticias', [NewsController::class, 'index'])->name('news.index');
Route::get('/noticias/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/videos', [VideosController::class, 'index'])->name('videos.index');
Route::get('/equipo', [TeamController::class, 'index'])->name('team.index');
Route::get('/equipo/{teamMember}/foto', [TeamController::class, 'photo'])->name('team.photo');
Route::get('/testimonios', [TestimonialsController::class, 'index'])->name('testimonials.index');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('login.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/password/forgot', [PasswordResetOtpController::class, 'requestForm'])->name('password.request');
    Route::post('/password/forgot', [PasswordResetOtpController::class, 'requestCode'])
        ->middleware('throttle:5,1')->name('password.request.send');
    Route::get('/password/otp', [PasswordResetOtpController::class, 'verifyForm'])->name('password.otp.form');
    Route::post('/password/otp/verify', [PasswordResetOtpController::class, 'verify'])
        ->middleware('throttle:10,1')->name('password.otp.verify');
    Route::post('/password/otp/resend', [PasswordResetOtpController::class, 'resend'])
        ->middleware('throttle:5,1')->name('password.otp.resend');
    Route::get('/password/reset', [PasswordResetOtpController::class, 'resetForm'])->name('password.reset.form');
    Route::post('/password/reset', [PasswordResetOtpController::class, 'updatePassword'])
        ->middleware('throttle:5,1')->name('password.reset.update');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'active.user'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('audit', [AuditLogController::class, 'index'])->middleware('can:audit.view')->name('audit.index');
    Route::resource('users', UserController::class)->except(['show', 'destroy'])
        ->middlewareFor('index', 'can:users.view')
        ->middlewareFor(['create', 'store'], 'can:users.create')
        ->middlewareFor(['edit', 'update'], 'can:users.update');
    Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->middleware('can:users.update')->name('users.status');
    Route::get('seo', [SeoSettingsController::class, 'edit'])->middleware('can:settings.view')->name('seo.edit');
    Route::put('seo', [SeoSettingsController::class, 'update'])->middleware('can:settings.update')->name('seo.update');
    Route::get('settings', [SiteSettingsController::class, 'edit'])->middleware('can:settings.view')->name('settings.edit');
    Route::put('settings', [SiteSettingsController::class, 'update'])->middleware('can:settings.update')->name('settings.update');
    Route::get('settings/mail', [MailSettingController::class, 'edit'])->middleware('can:settings.view')->name('settings.mail.edit');
    Route::put('settings/mail', [MailSettingController::class, 'update'])->middleware('can:settings.update')->name('settings.mail.update');
    Route::post('settings/mail/test', [MailSettingController::class, 'sendTest'])->middleware(['can:settings.update', 'throttle:3,1'])->name('settings.mail.test');
    Route::patch('social-links/{socialLink}/toggle', [SocialLinkController::class, 'toggle'])->middleware('can:settings.update')->name('social-links.toggle');
    Route::resource('social-links', SocialLinkController::class)->except('show')->parameters(['social-links' => 'socialLink'])
        ->middlewareFor('index', 'can:settings.view')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'can:settings.update');
    Route::get('about', [AboutPageController::class, 'edit'])->middleware('can:cms.manage-other-modules')->name('about.edit');
    Route::put('about', [AboutPageController::class, 'update'])->middleware('can:cms.manage-other-modules')->name('about.update');
    Route::middleware('can:cms.manage-other-modules')->group(function (): void {
        Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
        Route::resource('categories', CategoryController::class)->except('show');
        Route::get('videos', [VideoController::class, 'index'])->name('videos.index');
        Route::patch('videos/{video}/toggle', [VideoController::class, 'toggle'])->name('videos.toggle');
        Route::resource('videos', VideoController::class)->except(['index', 'show']);
        Route::patch('team/{teamMember}/toggle', [TeamMemberController::class, 'toggle'])->name('team.toggle');
        Route::get('team/{teamMember}/photo', [TeamMemberController::class, 'photo'])->name('team.photo');
        Route::resource('team', TeamMemberController::class)->except('show')->parameters(['team' => 'teamMember']);
        Route::patch('testimonials/{testimonial}/toggle', [TestimonialController::class, 'toggle'])->name('testimonials.toggle');
        Route::resource('testimonials', TestimonialController::class)->except('show');
        Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::patch('contact-messages/{contactMessage}', [ContactMessageController::class, 'update'])->name('contact-messages.update');
    });
    Route::get('posts/{post}/preview', [PostController::class, 'preview'])->middleware('can:posts.view')->name('posts.preview');
    Route::patch('posts/{post}/publish', [PostController::class, 'publish'])->middleware('can:posts.publish')->name('posts.publish');
    Route::patch('posts/{post}/unpublish', [PostController::class, 'unpublish'])->middleware('can:posts.publish')->name('posts.unpublish');
    Route::patch('posts/{post}/archive', [PostController::class, 'archive'])->middleware('can:posts.publish')->name('posts.archive');
    Route::resource('posts', PostController::class)->except('show')
        ->middlewareFor('index', 'can:posts.view')
        ->middlewareFor(['create', 'store'], 'can:posts.create')
        ->middlewareFor(['edit', 'update'], 'can:posts.update')
        ->middlewareFor('destroy', 'can:posts.delete');
    Route::get('media/{media}/file', [MediaController::class, 'file'])->middleware('can:media.view')->name('media.file');
    Route::post('media', [MediaController::class, 'store'])->middleware('can:media.upload')->name('media.store');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->middleware('can:media.delete')->name('media.destroy');
    Route::get('media', [MediaController::class, 'index'])->middleware('can:media.view')->name('media.index');
    Route::get('banners/{banner}/preview', [BannerController::class, 'preview'])->middleware('can:banners.view')->name('banners.preview');
    Route::patch('banners/{banner}/toggle', [BannerController::class, 'toggle'])->middleware('can:banners.update')->name('banners.toggle');
    Route::resource('banners', BannerController::class)->except('show')
        ->middlewareFor('index', 'can:banners.view')
        ->middlewareFor(['create', 'store'], 'can:banners.create')
        ->middlewareFor(['edit', 'update'], 'can:banners.update')
        ->middlewareFor('destroy', 'can:banners.delete');
    Route::resource('services', ServiceController::class)->except('show')
        ->middlewareFor('index', 'can:services.view')
        ->middlewareFor(['create', 'store'], 'can:services.create')
        ->middlewareFor(['edit', 'update'], 'can:services.update')
        ->middlewareFor('destroy', 'can:services.delete');
    Route::patch('services/{service}/toggle', [ServiceController::class, 'toggle'])->middleware('can:services.update')->name('services.toggle');
});
