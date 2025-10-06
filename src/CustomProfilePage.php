<?php

namespace HuseyinFiliz\CustomProfilePage;

use Flarum\Database\AbstractModel;
use Flarum\User\User;

class CustomProfilePage extends AbstractModel
{
    protected $table = 'custom_profile_pages';

    protected $fillable = ['content', 'updated_at'];

    public $timestamps = false;

    /**
     * Kullanıcı ile ilişki
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}