<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use HuseyinFiliz\CustomProfilePage\Api\Serializer\CustomProfilePageSerializer;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class UpdateCustomPageController extends AbstractShowController
{
    public $serializer = CustomProfilePageSerializer::class;

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $userId = Arr::get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);
        
        // Kullanıcıyı bul
        $user = User::findOrFail($userId);
        
        // Yetki kontrolü - sadece kendi sayfasını düzenleyebilir
        if ($actor->id != $userId) {
            throw new \Flarum\User\Exception\PermissionDeniedException();
        }
        
        // Edit yetkisi kontrolü
        if (!$actor->hasPermission('user.editOwnCustomPage')) {
            throw new \Flarum\User\Exception\PermissionDeniedException();
        }
        
        // Sayfayı bul
        $page = CustomProfilePage::where('user_id', $userId)->firstOrFail();
        
        $attributes = Arr::get($request->getParsedBody(), 'data.attributes', []);
        
        if (isset($attributes['content'])) {
            $page->content = $attributes['content'];
        }
        
        $page->save();
        
        return $page;
    }
}