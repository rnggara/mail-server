<?php

namespace Tests\Unit;

use App\Services\TemplateRenderer;
use Tests\TestCase;

class TemplateRendererTest extends TestCase
{
    public function test_render_replaces_variables()
    {
        $renderer = new TemplateRenderer();
        $result = $renderer->render(null, ['name' => 'Budi'], '<p>Halo {{name}}</p>', 'Halo {{name}}', 'Subjek {{name}}');
        $this->assertEquals('<p>Halo Budi</p>', $result['html']);
        $this->assertEquals('Halo Budi', $result['text']);
        $this->assertEquals('Subjek Budi', $result['subject']);
    }
}

