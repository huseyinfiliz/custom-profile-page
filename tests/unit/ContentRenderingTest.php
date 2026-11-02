<?php

namespace HuseyinFiliz\CustomProfilePage\Tests\Unit;

use Flarum\Settings\SettingsRepositoryInterface;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;
use Illuminate\Container\Container;
use Mockery;
use PHPUnit\Framework\TestCase;

class ContentRenderingTest extends TestCase
{
    protected function tearDown(): void
    {
        Container::setInstance(null);
        Mockery::close();
        parent::tearDown();
    }

    private function page(string $format, string $content, string $tags = 'a,p,strong,img'): CustomProfilePage
    {
        $settings = Mockery::mock(SettingsRepositoryInterface::class);
        $settings->shouldReceive('get')->with('huseyinfiliz-custom-profile-page.content_format', 'markdown')->andReturn($format);
        $settings->shouldReceive('get')->with('huseyinfiliz-custom-profile-page.allowed_html_tags', Mockery::any())->andReturn($tags);
        Container::getInstance()->instance(SettingsRepositoryInterface::class, $settings);

        return new CustomProfilePage(['content' => $content]);
    }

    public function test_html_removes_executable_attributes_and_urls_but_preserves_safe_markup(): void
    {
        $page = $this->page('html', '<p onclick="alert(1)"><strong>Safe</strong><a href="javascript:alert(1)">bad</a><img src="x" onerror="alert(1)"><script>alert(1)</script><a href="https://example.org">good</a></p>');
        $html = $page->content_html;
        $this->assertStringContainsString('<strong>Safe</strong>', $html);
        $this->assertStringContainsString('href="https://example.org"', $html);
        foreach (['onclick', 'onerror', 'javascript:', '<script'] as $unsafe) {
            $this->assertStringNotContainsString($unsafe, $html);
        }
    }

    public function test_html_respects_the_configured_tag_allowlist(): void
    {
        $html = $this->page('html', '<p><strong>Keep</strong><a href="https://example.org">text</a></p>', 'strong')->content_html;
        $this->assertStringContainsString('<strong>Keep</strong>', $html);
        $this->assertStringNotContainsString('<a', $html);
        $this->assertStringNotContainsString('<p', $html);
    }

    public function test_plain_text_is_escaped_and_preserves_newlines(): void
    {
        $html = $this->page('text', "<script>bad</script>\nnext")->content_html;
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('<br', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_zero_is_valid_content(): void
    {
        $this->assertSame('0', $this->page('text', '0')->content_html);
    }
}
