<?php

use Flarum\Database\Migration;
use Flarum\Group\Group;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        if (! $schema->hasTable('custom_profile_pages')) {
            $schema->create('custom_profile_pages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->unique();
                $table->text('content')->nullable();

                // ✅ Timestamps ekle
                $table->timestamps(); // created_at ve updated_at otomatik ekler

                // Foreign key
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });

            // Defaults belong only to fresh installs, never to an upgrade that
            // may have deliberately restricted or revoked these permissions.
            $permissions = Migration::addPermissions([
                'user.viewCustomPage' => [Group::GUEST_ID, Group::MEMBER_ID],
                'user.editOwnCustomPage' => [Group::MEMBER_ID],
                'user.moderateCustomPage' => [Group::MODERATOR_ID],
            ]);
            $permissions['up']($schema);
        }
    },

    'down' => function (Builder $schema) {
        $schema->dropIfExists('custom_profile_pages');
    },
];
