<?php

use App\Models\Page;

it('renders an active page by slug', function () {
    $page = Page::factory()->create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'content' => '<p>About content</p>',
        'is_active' => true,
    ]);

    $this->get(route('pages.show', ['slug' => $page->slug]))
        ->assertOk()
        ->assertViewIs('pages.show')
        ->assertViewHas('page', fn (Page $viewPage): bool => $viewPage->is($page))
        ->assertSee('About Us')
        ->assertSee('About content');
});

it('returns not found for inactive pages', function () {
    $page = Page::factory()->create([
        'slug' => 'privacy-policy',
        'is_active' => false,
    ]);

    $this->get(route('pages.show', ['slug' => $page->slug]))
        ->assertNotFound();
});

it('returns not found for unknown slug', function () {
    $this->get(route('pages.show', ['slug' => 'missing-page']))
        ->assertNotFound();
});
