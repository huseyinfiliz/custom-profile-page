<?php

namespace HuseyinFiliz\CustomProfilePage;

use Flarum\Database\AbstractModel;
use Flarum\Database\ScopeVisibilityTrait;
use Flarum\Foundation\EventGeneratorTrait;
use Flarum\Formatter\Formatter;
use Flarum\User\User;

class CustomProfilePage extends AbstractModel
{
    use EventGeneratorTrait;
    use ScopeVisibilityTrait;

    protected $table = 'custom_profile_pages';

    protected $dates = ['created_at', 'updated_at'];

    public $timestamps = true;

    protected $fillable = ['user_id', 'content'];

    public function getContentHtmlAttribute()
    {
        if (!$this->content) {
            return '';
        }

        // ✅ Flarum'un TextFormatter instance'ını al
        static $formatter = null;
        
        if ($formatter === null) {
            $formatter = app(Formatter::class);
        }

        // ✅ User context ile parse et (FoF Formatting için gerekli)
        $context = $this->user;

        // Parse et
        $xml = $formatter->parse($this->content, $context);
        
        // Render et
        $html = $formatter->render($xml, $context);
        
        return $html;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}