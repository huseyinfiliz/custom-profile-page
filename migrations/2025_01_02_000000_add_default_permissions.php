<?php

use Flarum\Database\Migration;
use Flarum\Group\Group;

return Migration::addPermissions([
    'user.viewCustomPage' => [Group::GUEST_ID, Group::MEMBER_ID],
    'user.editOwnCustomPage' => [Group::MEMBER_ID],
    'user.moderateCustomPage' => [Group::MODERATOR_ID],
]);
