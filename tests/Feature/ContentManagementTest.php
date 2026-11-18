<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use App\Models\Partner;
use App\Models\SiteSetting;
use App\Models\User;
use App\Filament\Resources\EditorialItemResource\Pages\ListEditorialItems;
use App\Filament\Resources\DonationResource\Pages\ListDonations;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_configured_information_pages_are_seeded_as_editable_content(): void
    {
        $page = ContentPage::where('path', 'news')->firstOrFail();
        $page->update(['title' => ['en' => 'Community updates'], 'body' => ['en' => 'A published update.']]);

        $this->get('/news')->assertOk()->assertSee('Community updates')->assertSee('A published update.');
        $this->assertSame('Community updates', $page->fresh()->translated('title'));
    }

    public function test_unpublished_content_page_is_hidden(): void
    {
        ContentPage::where('path', 'news')->update(['is_published' => false]);

        $this->get('/news')->assertNotFound();
    }

    public function test_published_partners_have_public_listing_and_detail_pages(): void
    {
        $partner = Partner::create(['name' => 'Community Health Network', 'short_description' => 'A trusted partner.', 'description' => 'Working together.', 'active' => true, 'show_on_website' => true]);

        $this->get('/about/partners')->assertOk()->assertSee('Community Health Network');
        $this->get('/about/partners/'.$partner->slug)->assertOk()->assertSee('Working together.');
        $partner->update(['show_on_website' => false]);
        $this->get('/about/partners/'.$partner->slug)->assertNotFound();
    }

    public function test_brand_assets_from_settings_are_used_by_the_public_layout(): void
    {
        $settings = SiteSetting::where('key', 'global')->firstOrFail();
        $settings->update(['data' => array_replace($settings->data, [
            'logo' => 'images/brand.webp', 'logo_alt' => 'UGHI logo',
            'favicon' => 'images/favicon.webp', 'favicon_alt' => 'UGHI favicon',
        ])]);

        $this->get('/')->assertOk()->assertSee('/storage/images/brand.webp')->assertSee('alt="UGHI logo"', false)->assertSee('image/webp', false)->assertSee('/storage/images/favicon.webp');
    }

    public function test_editorial_item_create_action_opens_in_a_dialog(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole('Administrator');

        Livewire::actingAs($user)->test(ListEditorialItems::class)
            ->assertTableActionExists('create', fn ($action): bool => $action->hasModal());
    }

    public function test_resource_create_actions_open_in_dialogs_by_default(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole('Administrator');

        Livewire::actingAs($user)->test(ListDonations::class)
            ->assertActionExists('create', fn ($action): bool => $action->hasModal());
    }
}
