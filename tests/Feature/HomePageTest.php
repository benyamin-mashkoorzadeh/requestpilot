<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_the_home_page_contains_the_inquiry_form(): void
    {
        $this->withoutVite();

        $this->get('/')
            ->assertOk()
            ->assertSee('RequestPilot')
            ->assertSee('How can we help?')
            ->assertSee('Send inquiry');
    }
}
