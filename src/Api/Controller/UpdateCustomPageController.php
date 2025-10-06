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
        $actor = RequestUtil::getActor($request);
        $userId = Arr::get($request->getQueryParams(), 'id');
        
        $user = User::findOrFail($userId);

        // Kendi sayfasını düzenleyebilir mi?
        $actor->assertCan('editOwnCustomPage', $user);

        $page = CustomProfilePage::where('user_id', $userId)->firstOrFail();

        $content = Arr::get($request->getParsedBody(), 'data.attributes.content');

        if ($content !== null) {
            $page->content = $content;
            $page->updated_at = now();
            $page->save();
        }

        return $page;
    }
}