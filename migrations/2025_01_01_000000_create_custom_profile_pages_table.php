<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        if (!$schema->hasTable('custom_profile_pages')) {
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
        }
    },

    'down' => function (Builder $schema) {
        $schema->dropIfExists('custom_profile_pages');
    },
];