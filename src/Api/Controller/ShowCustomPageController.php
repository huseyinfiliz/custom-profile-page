<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use HuseyinFiliz\CustomProfilePage\Api\Serializer\CustomProfilePageSerializer;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ShowCustomPageController extends AbstractShowController
{
    public $serializer = CustomProfilePageSerializer::class;

    protected function data(ServerRequestInterface $request, Document $document)
    {
        $userId = array_get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);

        $user = User::findOrFail($userId);

        // İçeriği görebilir mi kontrol et
        $actor->assertCan('viewCustomPage', $user);

        $page = CustomProfilePage::where('user_id', $userId)->first();

        if (!$page) {
            // Sayfa yoksa boş döndür
            return null;
        }

        return $page;
    }
}