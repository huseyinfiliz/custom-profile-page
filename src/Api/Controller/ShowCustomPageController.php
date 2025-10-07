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

class ShowCustomPageController extends AbstractShowController
{
    public $serializer = CustomProfilePageSerializer::class;

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $userId = Arr::get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);
        
        // ✅ View yetkisi kontrolü
        if (!$actor || !$actor->hasPermission('viewCustomPage')) {
            throw new PermissionDeniedException();
        }
        
        // Sayfayı bul
        $page = CustomProfilePage::where('user_id', $userId)->first();
        
        // ✅ Sayfa yoksa boş response dön (404 değil, frontend'de handle edilecek)
        if (!$page) {
            return null;
        }
        
        return $page;
    }
}