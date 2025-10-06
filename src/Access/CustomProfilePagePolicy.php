<?php

namespace HuseyinFiliz\CustomProfilePage\Access;

use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

class CustomProfilePagePolicy extends AbstractPolicy
{
    /**
     * Kullanıcının başkasının özel sayfasını görüntüleyebilir mi?
     */
    public function viewCustomPage(User $actor, User $user)
    {
        // Herkes görebilir
        return $this->allow();
    }

    /**
     * Kullanıcı kendi özel sayfasını düzenleyebilir mi?
     */
    public function editOwnCustomPage(User $actor, User $user)
    {
        // Sadece kendi sayfasını düzenleyebilir
        // Admin panelde "user.editOwnCustomPage" izni verilmiş gruplara açık
        if ($actor->id === $user->id) {
            return $this->allow();
        }

        return $this->deny();
    }
}