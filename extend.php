<?php

/*
 * This file is part of huseyinfiliz/custom-profile-page.
 *
 * Copyright (c) 2025 Hüseyin Filiz.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace HuseyinFiliz\CustomProfilePage;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\Extend;
use Flarum\User\User;
use HuseyinFiliz\CustomProfilePage\Access\CustomProfilePagePolicy;
use HuseyinFiliz\CustomProfilePage\Api\Controller\CreateCustomPageController;
use HuseyinFiliz\CustomProfilePage\Api\Controller\ShowCustomPageController;
use HuseyinFiliz\CustomProfilePage\Api\Controller\UpdateCustomPageController;
use HuseyinFiliz\CustomProfilePage\Api\Serializer\CustomProfilePageSerializer;

return [
    // Frontend
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),
    
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),
    
    // Locales
    new Extend\Locales(__DIR__.'/locale'),

    // API Routes
    (new Extend\Routes('api'))
        ->get('/users/{id}/custom-page', 'custom-profile-page.show', ShowCustomPageController::class)
        ->post('/users/{id}/custom-page', 'custom-profile-page.create', CreateCustomPageController::class)
        ->patch('/users/{id}/custom-page', 'custom-profile-page.update', UpdateCustomPageController::class),

    // User Model Relationship
    (new Extend\Model(User::class))
        ->hasOne('customPage', CustomProfilePage::class, 'user_id'),

    // Policy
    (new Extend\Policy())
        ->modelPolicy(User::class, CustomProfilePagePolicy::class),

    // Settings
    (new Extend\Settings())
        ->default('huseyinfiliz-custom-profile-page.tab_icon', 'fas fa-file-alt')
        ->default('huseyinfiliz-custom-profile-page.tab_title', 'Custom Page')
        ->default('huseyinfiliz-custom-profile-page.content_format', 'markdown'),

    // User Serializer - customPage relationship ekle
    (new Extend\ApiSerializer(UserSerializer::class))
        ->hasOne('customPage', CustomProfilePageSerializer::class),
];