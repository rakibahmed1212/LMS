<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_basic_public_pages_render(): void
    {
        foreach (['about', 'contact', 'faq', 'privacy', 'terms'] as $path) {
            $this->get('/'.$path)->assertOk();
        }
    }
}
