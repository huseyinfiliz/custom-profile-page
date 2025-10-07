<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Controller;

use Flarum\Api\Controller\AbstractCreateController;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use Flarum\User\Exception\PermissionDeniedException;
use HuseyinFiliz\CustomProfilePage\Api\Serializer\CustomProfilePageSerializer;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class CreateCustomPageController extends AbstractCreateController
{
    public $serializer = CustomProfilePageSerializer::class;

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $userId = Arr::get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);
        
        // Kullanıcıyı bul
        $user = User::findOrFail($userId);
        
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
        
        // ✅ Zaten var mı kontrol et
        $existingPage = CustomProfilePage::where('user_id', $userId)->first();
        if ($existingPage) {
            throw new \Exception('Custom page already exists. Use PATCH to update.');
        }
        
        $attributes = Arr::get($request->getParsedBody(), 'data.attributes', []);
        $content = Arr::get($attributes, 'content', '');
        
        // ✅ Yeni sayfa oluştur
        $page = new CustomProfilePage();
        $page->user_id = $userId;
        $page->content = $content;
        $page->save();
        
        return $page;
    }
}