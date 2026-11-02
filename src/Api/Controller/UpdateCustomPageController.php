<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Controller;

use Flarum\Http\RequestUtil;
use HuseyinFiliz\CustomProfilePage\Api\Serializer;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;
use HuseyinFiliz\CustomProfilePage\CustomProfilePageValidator;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateCustomPageController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $userId = (int) Arr::get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);

        $page = CustomProfilePage::where('user_id', $userId)->firstOrFail();

        $actor->assertCan('update', $page);

        $attributes = Arr::get($request->getParsedBody(), 'data.attributes', []);

        if (array_key_exists('content', $attributes)) {
            resolve(CustomProfilePageValidator::class)->assertValid(['content' => $attributes['content']]);
            $page->content = $attributes['content'];
            $page->save();
        }

        return new JsonResponse([
            'data' => Serializer::data($page),
        ]);
    }
}
