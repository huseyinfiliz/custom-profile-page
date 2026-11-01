<?php
namespace HuseyinFiliz\CustomProfilePage;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Extend;
use Flarum\User\User;
use HuseyinFiliz\CustomProfilePage\Access\CustomProfilePagePolicy;
use HuseyinFiliz\CustomProfilePage\Api\Controller\CreateCustomPageController;
use HuseyinFiliz\CustomProfilePage\Api\Controller\ShowCustomPageController;
use HuseyinFiliz\CustomProfilePage\Api\Controller\UpdateCustomPageController;
use HuseyinFiliz\CustomProfilePage\Api\Serializer\CustomProfilePageSerializer;
use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;

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
    
    (new Extend\Settings())
        ->default('huseyinfiliz-custom-profile-page.tab_icon', 'fas fa-file-alt')
        ->default('huseyinfiliz-custom-profile-page.tab_title', 'Custom Page')
        ->default('huseyinfiliz-custom-profile-page.url_slug', 'customPage')
        ->default('huseyinfiliz-custom-profile-page.content_format', 'markdown')
        ->default('huseyinfiliz-custom-profile-page.allowed_html_tags', 'b,i,u,strong,em,a,br,p,ul,ol,li,blockquote,code,pre')
        ->default('huseyinfiliz-custom-profile-page.default_template', '')
        ->default('huseyinfiliz-custom-profile-page.enable_media_embeds', '1')
        
        // Serialize İşlemleri (Ayarları Frontend'e Gönderme)
        ->serializeToForum('huseyinfiliz-custom-profile-page.tab_title', 'huseyinfiliz-custom-profile-page.tab_title')
        ->serializeToForum('huseyinfiliz-custom-profile-page.tab_icon', 'huseyinfiliz-custom-profile-page.tab_icon')
        ->serializeToForum('huseyinfiliz-custom-profile-page.url_slug', 'huseyinfiliz-custom-profile-page.url_slug')
        ->serializeToForum('huseyinfiliz-custom-profile-page.content_format', 'huseyinfiliz-custom-profile-page.content_format')
        ->serializeToForum('huseyinfiliz-custom-profile-page.allowed_html_tags', 'huseyinfiliz-custom-profile-page.allowed_html_tags')
        ->serializeToForum('huseyinfiliz-custom-profile-page.default_template', 'huseyinfiliz-custom-profile-page.default_template')
        
        // Son satırı özellikle düzelttim: Boolean dönüşümü ekledik.
        ->serializeToForum(
            'huseyinfiliz-custom-profile-page.enable_media_embeds', 
            'huseyinfiliz-custom-profile-page.enable_media_embeds', 
            function ($value) {
                return (bool) $value;
            }
        ),
    
    // @TODO: Replace with the new implementation https://docs.flarum.org/2.x/extend/api#extending-api-resources
    (new Extend\ApiSerializer(UserSerializer::class))
        ->hasOne('customPage', CustomProfilePageSerializer::class),
    
    // @TODO: Replace with the new implementation https://docs.flarum.org/2.x/extend/api#extending-api-resources
    (new Extend\ApiSerializer(ForumSerializer::class))
        ->attributes(function (ForumSerializer $serializer) {
            $actor = $serializer->getActor();
            $attributes = [];

            if ($actor !== null) {
                $attributes['canEditOwnCustomPage'] = $actor->hasPermission('user.editOwnCustomPage');
                $attributes['canViewCustomPage'] = $actor->hasPermission('user.viewCustomPage');
                $attributes['canModerateCustomPage'] = $actor->hasPermission('user.moderateCustomPage');
            } else {
                $attributes['canEditOwnCustomPage'] = false;
                $attributes['canViewCustomPage'] = true;
                $attributes['canModerateCustomPage'] = false;
            }
            
            return $attributes;
        }),
    new Extend\ApiResource(Api\Resource\CustomProfilePageResource::class),
];