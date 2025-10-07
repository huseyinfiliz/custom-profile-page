<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Serializer;

use Flarum\Api\Serializer\AbstractSerializer;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;

class CustomProfilePageSerializer extends AbstractSerializer
{
    protected $type = 'custom-profile-pages';

    protected function getDefaultAttributes($page)
    {
        if (!($page instanceof CustomProfilePage)) {
            throw new \InvalidArgumentException('Model must be an instance of CustomProfilePage');
        }

        $attributes = [
            'id' => $page->id,
            'userId' => $page->user_id,
            'content' => $page->content,
            'createdAt' => $this->formatDate($page->created_at),
            'updatedAt' => $this->formatDate($page->updated_at),
        ];

        // ✅ Format'a göre HTML ekle
        $format = app('flarum.settings')->get('huseyinfiliz-custom-profile-page.content_format', 'markdown');
        
        if ($format === 'markdown') {
            // Markdown ise parse edilmiş HTML'i gönder
            $attributes['contentHtml'] = $page->content_html;
        } elseif ($format === 'html') {
            // HTML ise sanitize et
            $attributes['contentHtml'] = $this->sanitizeHtml($page->content);
        } else {
            // Plain text - nl2br
            $attributes['contentHtml'] = nl2br(e($page->content));
        }

        return $attributes;
    }

    protected function sanitizeHtml($html)
    {
        $allowedTags = app('flarum.settings')->get('huseyinfiliz-custom-profile-page.allowed_html_tags', 'b,i,u,strong,em,a,br,p,ul,ol,li,blockquote,code,pre');
        $tags = explode(',', $allowedTags);
        $tags = array_map('trim', $tags);
        
        return strip_tags($html, '<' . implode('><', $tags) . '>');
    }
}