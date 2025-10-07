<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Http\RequestUtil;
use Flarum\User\Exception\PermissionDeniedException;
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
        
        // Sayfayı bul
        $page = CustomProfilePage::where('user_id', $userId)->firstOrFail();
        
        // ✅ Yetki kontrolü
        $isModerator = $actor->hasPermission('user.moderateCustomPage');
        $isOwnPage = $actor->id == $userId;
        $canEditOwn = $actor->hasPermission('user.editOwnCustomPage');
        
        // Eğer moderatör değilse ve kendi sayfası değilse
        if (!$isModerator && !$isOwnPage) {
            throw new PermissionDeniedException();
        }
        
        // Eğer kendi sayfası ama edit yetkisi yoksa
        if ($isOwnPage && !$canEditOwn && !$isModerator) {
            throw new PermissionDeniedException();
        }
        
        // ✅ İçeriği güncelle
        $attributes = Arr::get($request->getParsedBody(), 'data.attributes', []);
        
        if (isset($attributes['content'])) {
            $page->content = $attributes['content'];
            $page->save();
        }
        
        return $page;
    }
}