<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    private function clean(string $html): string
    {
        return HtmlSanitizer::articleBody($html);
    }

    public function test_scripts_and_event_handlers_are_removed(): void
    {
        $out = $this->clean('<p onclick="x()">Bonjour</p><script>alert(1)</script><img src="/a.png" onerror="alert(1)">');

        $this->assertStringNotContainsString('script', $out);
        $this->assertStringNotContainsString('onclick', $out);
        $this->assertStringNotContainsString('onerror', $out);
        $this->assertStringContainsString('<p>Bonjour</p>', $out);
        $this->assertStringContainsString('src="/a.png"', $out);
    }

    #[DataProvider('dangerousLinks')]
    public function test_dangerous_link_schemes_are_dropped(string $href): void
    {
        $out = $this->clean('<a href="'.$href.'">lien</a>');

        $this->assertStringNotContainsString('href', $out);
        $this->assertStringContainsString('lien', $out);
    }

    public static function dangerousLinks(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'majuscules' => ['JaVaScRiPt:alert(1)'],
            'entites' => ['&#106;avascript:alert(1)'],
            'tabulation' => ["java\tscript:alert(1)"],
            'data html' => ['data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=='],
            'vbscript' => ['vbscript:msgbox(1)'],
        ];
    }

    public function test_safe_links_are_kept_and_blank_targets_get_noopener(): void
    {
        $out = $this->clean('<a href="https://exemple.ci/page" target="_blank">ok</a><a href="mailto:a@b.ci">m</a><a href="/interne#x">i</a>');

        $this->assertStringContainsString('href="https://exemple.ci/page"', $out);
        $this->assertStringContainsString('rel="noopener noreferrer"', $out);
        $this->assertStringContainsString('href="mailto:a@b.ci"', $out);
        $this->assertStringContainsString('href="/interne#x"', $out);
    }

    public function test_only_youtube_and_vimeo_iframes_survive_and_are_rebuilt(): void
    {
        $evil = $this->clean('<iframe src="https://evil.example/x"></iframe>');
        $this->assertStringNotContainsString('iframe', $evil);

        $js = $this->clean('<iframe src="javascript:alert(1)"></iframe>');
        $this->assertStringNotContainsString('iframe', $js);

        $yt = $this->clean('<iframe src="//www.youtube.com/embed/dQw4w9WgXcQ" onload="alert(1)" style="position:fixed"></iframe>');
        $this->assertStringContainsString('src="https://www.youtube.com/embed/dQw4w9WgXcQ"', $yt);
        $this->assertStringNotContainsString('onload', $yt);
        $this->assertStringNotContainsString('position', $yt);

        $vimeo = $this->clean('<iframe src="https://player.vimeo.com/video/123456789"></iframe>');
        $this->assertStringContainsString('player.vimeo.com/video/123456789', $vimeo);
    }

    public function test_dangerous_elements_are_removed_with_their_content(): void
    {
        $out = $this->clean('<p>a</p><svg onload="x()"><circle/></svg><form action="/x"><input name="p"></form><object data="x"></object><style>body{display:none}</style><p>b</p>');

        $this->assertSame('<p>a</p><p>b</p>', $out);
    }

    public function test_style_attribute_keeps_safe_properties_only(): void
    {
        $out = $this->clean('<span style="color:#ff0000;font-size:18px;position:fixed;background:url(javascript:alert(1));width:expression(alert(1))">t</span>');

        $this->assertStringContainsString('color: #ff0000', $out);
        $this->assertStringContainsString('font-size: 18px', $out);
        $this->assertStringNotContainsString('position', $out);
        $this->assertStringNotContainsString('url', $out);
        $this->assertStringNotContainsString('expression', $out);
    }

    public function test_editor_formatting_is_preserved(): void
    {
        $html = '<h1>T1</h1><h5>T5</h5><blockquote>c</blockquote><pre>code</pre><ul><li>a</li></ul>'
            .'<table class="table"><tbody><tr><td colspan="2">x</td></tr></tbody></table>'
            .'<p><strike>b</strike> <u>u</u> <span style="font-family: Georgia;">g</span></p><hr>';
        $out = $this->clean($html);

        foreach (['<h1>', '<h5>', '<blockquote>', '<pre>', '<ul>', '<table class="table">', 'colspan="2"', '<strike>', '<u>', '<hr>'] as $needle) {
            $this->assertStringContainsString($needle, $out);
        }
    }

    public function test_accents_and_typography_are_not_mangled(): void
    {
        $html = '<p>Été à Côte d’Ivoire — «test» œuvre &amp; co</p>';

        $this->assertSame($html, $this->clean($html));
    }

    public function test_unknown_tags_are_unwrapped_and_comments_removed(): void
    {
        $out = $this->clean('<!-- secret --><custom>texte</custom><font color="red">rouge</font><o:p>word</o:p>');

        $this->assertSame('texterougeword', $out);
    }

    public function test_images_accept_http_relative_and_inline_images_but_not_scripts(): void
    {
        $this->assertStringContainsString('src="/storage/a.png"', $this->clean('<img src="/storage/a.png" alt="a">'));
        $this->assertStringContainsString('src="https://cdn.exemple.ci/a.jpg"', $this->clean('<img src="https://cdn.exemple.ci/a.jpg">'));
        $this->assertStringNotContainsString('<img', $this->clean('<img src="javascript:alert(1)">'));
        $this->assertStringNotContainsString('<img', $this->clean('<img>'));
    }

    public function test_for_storage_returns_null_for_visually_empty_content(): void
    {
        $this->assertNull(HtmlSanitizer::forStorage(null));
        $this->assertNull(HtmlSanitizer::forStorage(''));
        $this->assertNull(HtmlSanitizer::forStorage('<p><br></p>'));
        $this->assertNull(HtmlSanitizer::forStorage('<p>&nbsp;</p>'));
        $this->assertSame('<p>ok</p>', HtmlSanitizer::forStorage('<p>ok</p><script>x()</script>'));
        $this->assertNotNull(HtmlSanitizer::forStorage('<p><img src="/storage/a.png"></p>'));
    }
}
