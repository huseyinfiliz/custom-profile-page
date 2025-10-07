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
    public function view(?User $actor, CustomProfilePage $page)
    {
        // Eğer actor yoksa (ziyaretçi) veya izni yoksa
        if (!$actor || !$actor->hasPermission('viewCustomPage')) {
            return $this->deny();
        }
        
        return $this->allow();
    }

    /**
     * Sayfa düzenleme yetkisi
     */
    public function update(User $actor, CustomProfilePage $page)
    {
        // 1. Moderatör ise her sayfayı düzenleyebilir
        if ($actor->hasPermission('user.moderateCustomPage')) {
            return $this->allow();
        }

        // 2. Kendi sayfası ise ve izni varsa düzenleyebilir
        if ($actor->id === $page->user_id && $actor->hasPermission('user.editOwnCustomPage')) {
            return $this->allow();
        }

        return $this->deny();
    }

    /**
     * Sayfa oluşturma yetkisi
     */
    public function create(User $actor, int $userId)
    {
        // 1. Moderatör ise herkes için sayfa oluşturabilir
        if ($actor->hasPermission('user.moderateCustomPage')) {
            return $this->allow();
        }

        // 2. Kendi sayfası için oluşturabilir
        if ($actor->id === $userId && $actor->hasPermission('user.editOwnCustomPage')) {
            return $this->allow();
        }

        return $this->deny();
    }
}