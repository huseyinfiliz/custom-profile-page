<?php

namespace HuseyinFiliz\CustomProfilePage;

use Flarum\Database\AbstractModel;
use Flarum\Database\ScopeVisibilityTrait;
use Flarum\Foundation\EventGeneratorTrait;
use Flarum\Formatter\Formatter;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $content
 * @property string $content_html
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property User|null $user
 */
class CustomProfilePage extends AbstractModel
{
    use EventGeneratorTrait;
    use ScopeVisibilityTrait;

    protected $table = 'custom_profile_pages';

    protected $casts = [
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    protected $fillable = ['user_id', 'content'];

    public function getContentHtmlAttribute(): string
    {
        if (empty($this->content)) {
            return '';
        }

        /** @var SettingsRepositoryInterface $settings */
        $settings = resolve(SettingsRepositoryInterface::class);
        $format = $settings->get('huseyinfiliz-custom-profile-page.content_format', 'markdown');

        if ($format === 'html') {
            return $this->sanitizeHtml($this->content);
        }

        if ($format === 'text') {
            return nl2br(e($this->content));
        }

        /** @var Formatter $formatter */
        $formatter = resolve(Formatter::class);
        $context = $this->user;

        $xml = $formatter->parse($this->content, $context);

        return $formatter->render($xml, $context);
    }

    public function sanitizeHtml(string $html): string
    {
        /** @var SettingsRepositoryInterface $settings */
        $settings = resolve(SettingsRepositoryInterface::class);
        $allowedTags = $settings->get(
            'huseyinfiliz-custom-profile-page.allowed_html_tags',
            'b,i,u,strong,em,a,br,p,ul,ol,li,blockquote,code,pre'
        );
        $tags = array_filter(array_map('trim', explode(',', $allowedTags)));

        return strip_tags($html, '<' . implode('><', $tags) . '>');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}