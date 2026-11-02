<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Controller;

use Flarum\Http\RequestUtil;
use Flarum\User\User;
use HuseyinFiliz\CustomProfilePage\Api\Serializer;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ShowCustomPageController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $userId = Arr::get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);

        User::whereVisibleTo($actor)->findOrFail($userId);
        $page = CustomProfilePage::where('user_id', $userId)->first();

        $actor->assertCan('view', $page ?? new CustomProfilePage(['user_id' => (int) $userId]));

        return new JsonResponse([
            'data' => $page ? Serializer::data($page) : null,
        ]);
    }
}
