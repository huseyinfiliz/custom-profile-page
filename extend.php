<?php

namespace HuseyinFiliz\CustomProfilePage;

use Flarum\Api\Context;
use Flarum\Api\Resource\ForumResource;
use Flarum\Api\Resource\UserResource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Flarum\User\User;
use HuseyinFiliz\CustomProfilePage\Access\CustomProfilePagePolicy;
use HuseyinFiliz\CustomProfilePage\Api\Controller\CreateCustomPageController;
use HuseyinFiliz\CustomProfilePage\Api\Controller\ShowCustomPageController;
use HuseyinFiliz\CustomProfilePage\Api\Controller\UpdateCustomPageController;
use HuseyinFiliz\CustomProfilePage\Api\Resource\CustomProfilePageResource;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Routes('api'))
        ->get('/users/{id}/custom-page', 'custom-profile-page.show', ShowCustomPageController::class)
        ->post('/users/{id}/custom-page', 'custom-profile-page.create', CreateCustomPageController::class)
        ->patch('/users/{id}/custom-page', 'custom-profile-page.update', UpdateCustomPageController::class),

    (new Extend\Model(User::class))
        ->hasOne('customPage', CustomProfilePage::class, 'user_id'),

    (new Extend\Policy())
        ->modelPolicy(CustomProfilePage::class, CustomProfilePagePolicy::class),

    (new Extend\ModelVisibility(CustomProfilePage::class))
        ->scope(function (User $actor, $query) {
            if ($actor->hasPermission('user.moderateCustomPage')) {
                return;
            }

            if (!$actor->hasPermission('user.viewCustomPage')) {
                if ($actor->id && $actor->hasPermission('user.editOwnCustomPage')) {
                    $query->where('user_id', $actor->id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }),

    (new Extend\Settings())
        ->default('huseyinfiliz-custom-profile-page.tab_icon', 'fas fa-file-alt')
        ->default('huseyinfiliz-custom-profile-page.tab_title', 'Custom Page')
        ->default('huseyinfiliz-custom-profile-page.url_slug', 'customPage')
        ->default('huseyinfiliz-custom-profile-page.content_format', 'markdown')
        ->default('huseyinfiliz-custom-profile-page.allowed_html_tags', 'b,i,u,strong,em,a,br,p,ul,ol,li,blockquote,code,pre')
        ->default('huseyinfiliz-custom-profile-page.default_template', '')
        ->default('huseyinfiliz-custom-profile-page.enable_media_embeds', '1')
        ->serializeToForum('huseyinfiliz-custom-profile-page.tab_title', 'huseyinfiliz-custom-profile-page.tab_title')
        ->serializeToForum('huseyinfiliz-custom-profile-page.tab_icon', 'huseyinfiliz-custom-profile-page.tab_icon')
        ->serializeToForum('huseyinfiliz-custom-profile-page.url_slug', 'huseyinfiliz-custom-profile-page.url_slug')
        ->serializeToForum('huseyinfiliz-custom-profile-page.content_format', 'huseyinfiliz-custom-profile-page.content_format')
        ->serializeToForum('huseyinfiliz-custom-profile-page.allowed_html_tags', 'huseyinfiliz-custom-profile-page.allowed_html_tags')
        ->serializeToForum('huseyinfiliz-custom-profile-page.default_template', 'huseyinfiliz-custom-profile-page.default_template')
        ->serializeToForum(
            'huseyinfiliz-custom-profile-page.enable_media_embeds',
            'huseyinfiliz-custom-profile-page.enable_media_embeds',
            fn ($value) => (bool) $value
        ),

    (new Extend\ApiResource(UserResource::class))
        ->fields(fn () => [
            Schema\Relationship\ToOne::make('customPage')
                ->type('custom-profile-pages')
                ->includable()
                ->nullable(),
        ]),

    (new Extend\ApiResource(ForumResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('canEditOwnCustomPage')
                ->get(fn ($model, Context $context) => $context->getActor()->hasPermission('user.editOwnCustomPage')),
            Schema\Boolean::make('canViewCustomPage')
                ->get(fn ($model, Context $context) => $context->getActor()->hasPermission('user.viewCustomPage')),
            Schema\Boolean::make('canModerateCustomPage')
                ->get(fn ($model, Context $context) => $context->getActor()->hasPermission('user.moderateCustomPage')),
        ]),

    new Extend\ApiResource(CustomProfilePageResource::class),
];