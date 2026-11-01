<?php

namespace HuseyinFiliz\CustomProfilePage\Access;

use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;

class CustomProfilePagePolicy extends AbstractPolicy
{
    /**
     * Permission kontrolü ile görüntüleme
     */
    public function view(User $actor, CustomProfilePage $page)
    {
        if ($actor->hasPermission('user.viewCustomPage')) {
            return $this->allow();
        }

        if ($actor->id && $actor->id === $page->user_id && $actor->hasPermission('user.editOwnCustomPage')) {
            return $this->allow();
        }

        return $this->deny();
    }

    /**
     * Sayfa düzenleme yetkisi
     */
    public function update(User $actor, CustomProfilePage $page)
    {
        if ($actor->hasPermission('user.moderateCustomPage')) {
            return $this->allow();
        }

        if ($actor->id && $actor->id === $page->user_id && $actor->hasPermission('user.editOwnCustomPage')) {
            return $this->allow();
        }

        return $this->deny();
    }

    /**
     * Sayfa oluşturma yetkisi
     */
    public function create(User $actor, CustomProfilePage|int $pageOrUserId)
    {
        if ($actor->hasPermission('user.moderateCustomPage')) {
            return $this->allow();
        }

        $userId = $pageOrUserId instanceof CustomProfilePage ? $pageOrUserId->user_id : (int) $pageOrUserId;

        if ($actor->id && $actor->id === $userId && $actor->hasPermission('user.editOwnCustomPage')) {
            return $this->allow();
        }

        return $this->deny();
    }

    /**
     * Sayfa silme yetkisi
     */
    public function delete(User $actor, CustomProfilePage $page)
    {
        if ($actor->hasPermission('user.moderateCustomPage')) {
            return $this->allow();
        }

        if ($actor->id && $actor->id === $page->user_id && $actor->hasPermission('user.editOwnCustomPage')) {
            return $this->allow();
        }

        return $this->deny();
    }
}