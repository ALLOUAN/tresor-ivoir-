<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Event;
use Tests\TestCase;

/**
 * Le contenu riche de l'éditeur est nettoyé à chaque écriture sur le modèle
 * (formulaire, auto-sauvegarde, seeders…), pas seulement à l'affichage.
 */
class RichContentSanitizationTest extends TestCase
{
    public function test_article_content_is_sanitized_when_assigned(): void
    {
        $article = new Article();
        $article->content_fr = '<p onclick="x()">Bonjour</p><script>alert(1)</script>';
        $article->content_en = '<p><br></p>';

        $this->assertSame('<p>Bonjour</p>', $article->getAttributes()['content_fr']);
        $this->assertNull($article->getAttributes()['content_en']);
    }

    public function test_mass_assignment_goes_through_the_sanitizer_too(): void
    {
        $article = (new Article())->fill(['content_fr' => '<a href="javascript:alert(1)">x</a>']);

        $this->assertStringNotContainsString('javascript', $article->getAttributes()['content_fr']);
    }

    public function test_event_description_is_sanitized_when_assigned(): void
    {
        $event = new Event();
        $event->description_fr = '<p>Programme</p><iframe src="https://evil.example"></iframe>';
        $event->description_en = '';

        $this->assertSame('<p>Programme</p>', $event->getAttributes()['description_fr']);
        $this->assertNull($event->getAttributes()['description_en']);
    }
}
