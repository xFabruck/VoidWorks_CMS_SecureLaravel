<?php

return [
    'catalog' => [
        'banners.view', 'banners.create', 'banners.update', 'banners.delete',
        'services.view', 'services.create', 'services.update', 'services.delete',
        'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish',
        'media.view', 'media.upload', 'media.delete',
        'users.view', 'users.create', 'users.update', 'users.delete',
        'settings.view', 'settings.update',
        'security.view', 'audit.view',
    ],
    'roles' => [
        'super_admin' => '*',
        'admin' => [
            'banners.view', 'banners.create', 'banners.update', 'banners.delete',
            'services.view', 'services.create', 'services.update', 'services.delete',
            'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish',
            'media.view', 'media.upload', 'media.delete',
            'users.view', 'users.create', 'users.update', 'users.delete',
            'settings.view', 'settings.update',
        ],
        'editor' => [
            'banners.view', 'banners.create', 'banners.update', 'banners.delete',
            'services.view', 'services.create', 'services.update', 'services.delete',
            'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish',
            'media.view', 'media.upload',
        ],
        'author' => [
            'posts.view', 'posts.create', 'posts.update',
            'media.view', 'media.upload',
        ],
    ],
];
