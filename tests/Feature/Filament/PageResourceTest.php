<?php

use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Filament\Resources\PageResource\Pages\ListPages;
use App\Models\Page;
use App\Models\User;
use App\Policies\PagePolicy;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('shows page records in filament list for admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $pages = Page::factory()->count(3)->create();

    $this->actingAs($admin);

    Livewire::test(ListPages::class)
        ->assertCanSeeTableRecords($pages);
});

it('creates a custom page from filament resource', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin);

    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Delivery Policy',
            'slug' => 'delivery-policy',
            'content' => '<p>Delivery details.</p>',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseHas('pages', [
        'title' => 'Delivery Policy',
        'slug' => 'delivery-policy',
        'is_active' => true,
    ]);
});

it('allows only admins in page policy', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'user']);
    $page = Page::factory()->create();
    $policy = app(PagePolicy::class);

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->update($admin, $page))->toBeTrue()
        ->and($policy->viewAny($user))->toBeFalse()
        ->and($policy->create($user))->toBeFalse()
        ->and($policy->update($user, $page))->toBeFalse();
});
