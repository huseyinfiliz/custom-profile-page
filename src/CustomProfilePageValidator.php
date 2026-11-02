<?php

namespace HuseyinFiliz\CustomProfilePage;

use Flarum\Foundation\AbstractValidator;

class CustomProfilePageValidator extends AbstractValidator
{
    protected array $rules = [
        'content' => ['nullable', 'string', 'max:65535'],
    ];
}
