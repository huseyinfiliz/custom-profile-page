<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Resource;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Api\Sort\SortColumn;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;
use Illuminate\Database\Eloquent\Builder;
use Tobyz\JsonApiServer\Context as OriginalContext;

/**
 * @extends Resource\AbstractDatabaseResource<CustomProfilePage>
 */
class CustomProfilePageResource extends Resource\AbstractDatabaseResource
{
    public function type(): string
    {
        return 'custom-profile-pages';
    }

    public function model(): string
    {
        return CustomProfilePage::class;
    }

    public function scope(Builder $query, OriginalContext $context): void
    {
        $actor = $context->getActor();
        $query->whereVisibleTo($actor);

        $params = $context->request->getQueryParams();
        if (isset($params['filter']['user'])) {
            $query->where('user_id', $params['filter']['user']);
        }
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->paginate(),
            Endpoint\Show::make()
                ->can('view'),
            Endpoint\Create::make()
                ->can('create')
                ->before(function (OriginalContext $context) {
                    $actor = $context->getActor();
                    $attrs = (array) ($context->body()['data']['attributes'] ?? []);
                    $userId = $attrs['userId'] ?? $actor->id;

                    $actor->assertCan('create', (int) $userId);
                }),
            Endpoint\Update::make()
                ->can('update'),
            Endpoint\Delete::make()
                ->can('delete'),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Integer::make('userId')
                ->property('user_id'),
            Schema\Str::make('content')
                ->nullable()
                ->writable(),
            Schema\Str::make('contentHtml')
                ->get(fn (CustomProfilePage $page) => $page->content_html),
            Schema\DateTime::make('createdAt')
                ->property('created_at'),
            Schema\DateTime::make('updatedAt')
                ->property('updated_at'),

            Schema\Relationship\ToOne::make('user')
                ->type('users')
                ->includable(),
        ];
    }

    public function sorts(): array
    {
        return [
            SortColumn::make('updatedAt', 'updated_at'),
            SortColumn::make('createdAt', 'created_at'),
        ];
    }

    public function newModel(OriginalContext $context): object
    {
        /** @var CustomProfilePage $model */
        $model = parent::newModel($context);
        $attrs = (array) ($context->body()['data']['attributes'] ?? []);
        $actor = $context->getActor();

        $userId = $attrs['userId'] ?? $actor->id;
        if ($actor->hasPermission('user.moderateCustomPage')) {
            $model->user_id = (int) $userId;
        } else {
            $model->user_id = $actor->id;
        }

        return $model;
    }
}
