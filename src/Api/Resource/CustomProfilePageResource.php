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
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->paginate(),
            Endpoint\Show::make()
                ->can('view'),
            Endpoint\Create::make()
                ->authenticated(),
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
                ->property('user_id')
                ->writableOnCreate()
                ->default(fn (OriginalContext $context) => $context->getActor()->id)
                ->requiredOnCreate()
                ->rule('exists:users,id')
                ->rule('unique:custom_profile_pages,user_id'),
            Schema\Str::make('content')
                ->nullable()
                ->writable()
                ->rule('max:65535'),
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

    public function create(object $model, OriginalContext $context): object
    {
        $context->getActor()->assertCan('create', $model);

        return parent::create($model, $context);
    }
}
