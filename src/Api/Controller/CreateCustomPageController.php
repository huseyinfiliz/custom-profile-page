<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Controller;

use Flarum\Api\Controller\AbstractCreateController;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
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
        
        // Yetki kontrolü - sadece kendi sayfasını oluşturabilir
        if ($actor->id != $userId) {
            throw new \Flarum\User\Exception\PermissionDeniedException();
        }
        
        // Edit yetkisi kontrolü
        if (!$actor->hasPermission('user.editOwnCustomPage')) {
            throw new \Flarum\User\Exception\PermissionDeniedException();
        }
        
        // Zaten var mı kontrol et
        $existingPage = CustomProfilePage::where('user_id', $userId)->first();
        if ($existingPage) {
            throw new \Exception('Custom page already exists. Use PATCH to update.');
        }
        
        $attributes = Arr::get($request->getParsedBody(), 'data.attributes', []);
        $content = Arr::get($attributes, 'content', '');
        
        // Yeni sayfa oluştur
        $page = new CustomProfilePage();
        $page->user_id = $userId;
        $page->content = $content;
        $page->save();
        
        return $page;
    }
}