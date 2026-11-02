<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Controller;

use Flarum\Http\RequestUtil;
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

        $page = CustomProfilePage::where('user_id', $userId)->first();

        if (! $page) {
            return new JsonResponse([
                'errors' => [
                    [
                        'status' => '404',
                        'code' => 'not_found',
                        'detail' => 'Custom profile page not found.',
                    ],
                ],
            ], 404);
        }

        $actor->assertCan('view', $page);

        return new JsonResponse([
            'data' => Serializer::data($page),
        ]);
    }
}
