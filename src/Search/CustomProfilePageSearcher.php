<?php

namespace HuseyinFiliz\CustomProfilePage\Search;

use Flarum\Search\Database\AbstractSearcher;
use Flarum\User\User;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;
use Illuminate\Database\Eloquent\Builder;

class CustomProfilePageSearcher extends AbstractSearcher
{
    public function getQuery(User $actor): Builder
    {
        return CustomProfilePage::whereVisibleTo($actor)->select('custom_profile_pages.*');
    }
}
