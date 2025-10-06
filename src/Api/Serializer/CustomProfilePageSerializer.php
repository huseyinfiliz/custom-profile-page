<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Serializer;

use Flarum\Api\Serializer\AbstractSerializer;
use Flarum\Api\Serializer\UserSerializer;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;

class CustomProfilePageSerializer extends AbstractSerializer
{
    protected $type = 'custom-profile-pages';

    /**
     * @param CustomProfilePage $page
     */
    protected function getDefaultAttributes($page)
    {
        return [
            'userId' => $page->user_id,
            'content' => $page->content,
            'updatedAt' => $this->formatDate($page->updated_at),
        ];
    }

    public function user($page)
    {
        return $this->hasOne($page, UserSerializer::class);
    }
}