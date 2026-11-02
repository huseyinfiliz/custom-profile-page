<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Controller;

use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use HuseyinFiliz\CustomProfilePage\Api\Serializer;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;
use HuseyinFiliz\CustomProfilePage\CustomProfilePageValidator;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateCustomPageController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $userId = (int) Arr::get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);

        User::findOrFail($userId);

        $page = new CustomProfilePage();
        $page->user_id = $userId;
        $actor->assertCan('create', $page);

        $existingPage = CustomProfilePage::where('user_id', $userId)->first();
        if ($existingPage) {
            throw new ValidationException([
                'message' => 'Custom page already exists. Use PATCH to update.',
            ]);
        }

        $attributes = Arr::get($request->getParsedBody(), 'data.attributes', []);
        $content = Arr::get($attributes, 'content', '');

        resolve(CustomProfilePageValidator::class)->assertValid(['content' => $content]);
        $page->content = $content;
        $page->save();

        return new JsonResponse([
            'data' => Serializer::data($page),
        ], 201);
    }
}
