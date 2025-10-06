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
        $actor = RequestUtil::getActor($request);
        $userId = Arr::get($request->getParsedBody(), 'data.attributes.userId');
        
        $user = User::findOrFail($userId);

        // Kendi sayfasını düzenleyebilir mi?
        $actor->assertCan('editOwnCustomPage', $user);

        // Zaten sayfa var mı kontrol et
        $existing = CustomProfilePage::where('user_id', $userId)->first();
        if ($existing) {
            throw new \Flarum\User\Exception\PermissionDeniedException('Page already exists');
        }

        $content = Arr::get($request->getParsedBody(), 'data.attributes.content', '');

        $page = CustomProfilePage::create([
            'user_id' => $userId,
            'content' => $content,
            'updated_at' => now()
        ]);

        return $page;
    }
}