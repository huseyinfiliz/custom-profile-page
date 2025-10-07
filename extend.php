<?php
namespace HuseyinFiliz\CustomProfilePage;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Extend;
use Flarum\User\User;
use Flarum\Settings\SettingsRepositoryInterface;
use HuseyinFiliz\CustomProfilePage\Access\CustomProfilePagePolicy;
use HuseyinFiliz\CustomProfilePage\Api\Controller\CreateCustomPageController;
use HuseyinFiliz\CustomProfilePage\Api\Controller\ShowCustomPageController;
use HuseyinFiliz\CustomProfilePage\Api\Controller\UpdateCustomPageController;
use HuseyinFiliz\CustomProfilePage\Api\Serializer\CustomProfilePageSerializer;

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
        ->default('huseyinfiliz-custom-profile-page.enable_media_embeds', '1'),
    
    (new Extend\ApiSerializer(UserSerializer::class))
        ->hasOne('customPage', CustomProfilePageSerializer::class),
    
    (new Extend\ApiSerializer(ForumSerializer::class))
        ->attributes(function (ForumSerializer $serializer) {
            $actor = $serializer->getActor();
            $settings = resolve(SettingsRepositoryInterface::class);
            
            // ✅ Permission kontrolleri
            $attributes = [
                'huseyinfiliz-custom-profile-page.tab_title' => $settings->get('huseyinfiliz-custom-profile-page.tab_title', 'Custom Page'),
                'huseyinfiliz-custom-profile-page.tab_icon' => $settings->get('huseyinfiliz-custom-profile-page.tab_icon', 'fas fa-file-alt'),
                'huseyinfiliz-custom-profile-page.url_slug' => $settings->get('huseyinfiliz-custom-profile-page.url_slug', 'customPage'),
                'huseyinfiliz-custom-profile-page.content_format' => $settings->get('huseyinfiliz-custom-profile-page.content_format', 'markdown'),
                'huseyinfiliz-custom-profile-page.allowed_html_tags' => $settings->get('huseyinfiliz-custom-profile-page.allowed_html_tags', 'b,i,u,strong,em,a,br,p,ul,ol,li,blockquote,code,pre'),
				'huseyinfiliz-custom-profile-page.enable_media_embeds' => (bool) $settings->get('huseyinfiliz-custom-profile-page.enable_media_embeds', true),
            ];
            
            if ($actor !== null) {
                $attributes['canEditOwnCustomPage'] = $actor->hasPermission('user.editOwnCustomPage');
                $attributes['canViewCustomPage'] = $actor->hasPermission('user.viewCustomPage');
                $attributes['canModerateCustomPage'] = $actor->hasPermission('user.moderateCustomPage');
            } else {
                // Guest kullanıcılar
                $attributes['canEditOwnCustomPage'] = false;
                $attributes['canViewCustomPage'] = true; // Guests can view
                $attributes['canModerateCustomPage'] = false;
            }
            
            return $attributes;
        }),
];