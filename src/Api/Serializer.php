<?php

namespace HuseyinFiliz\CustomProfilePage\Api;

use HuseyinFiliz\CustomProfilePage\CustomProfilePage;

class Serializer
{
    public static function data(CustomProfilePage $page): array
    {
        return [
            'type' => 'custom-profile-pages',
            'id' => (string) $page->id,
            'attributes' => [
                'userId' => (int) $page->user_id,
                'content' => $page->content,
                'contentHtml' => $page->content_html,
                'createdAt' => $page->created_at?->toIso8601String(),
                'updatedAt' => $page->updated_at?->toIso8601String(),
            ],
            'relationships' => [
                'user' => [
                    'data' => [
                        'type' => 'users',
                        'id' => (string) $page->user_id,
                    ],
                ],
            ],
        ];
    }
}
