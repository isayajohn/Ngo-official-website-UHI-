<?php

namespace Tests\Feature;

use App\Filament\Pages\EditProfile;
use App\Filament\Pages\Homepage;
use App\Filament\Pages\Settings;
use App\Filament\Resources\ProgrammeResource\Pages\CreateProgramme;
use App\Filament\Resources\ProgrammeResource\Pages\EditProgramme;
use App\Models\Programme;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\ImageStorage;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function officer(string $role = 'Content Manager'): User
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole($role);

        return $user;
    }

    public function test_homepage_and_programmes_render_seeded_content_without_fake_impact(): void
    {
        $this->get('/')->assertOk()->assertSee('Communities Leading,')->assertSee('Maternal, Newborn, Child and Adolescent Health')->assertDontSee('10,000')->assertDontSee('admin@ughi.local');
        $this->get('/programmes')->assertOk()->assertSee('Mental Health and Psychosocial Support');
        $this->assertDatabaseCount('programmes', 7);
    }

    public function test_all_public_navigation_destinations_resolve(): void
    {
        foreach (array_merge(['/about', '/about/vision-mission'], array_map(fn ($path) => '/'.$path, array_keys(config('ughi_pages')))) as $path) {
            $this->get($path)->assertOk();
        }
        foreach (Programme::all() as $programme) {
            $this->get('/programmes/'.$programme->slug)->assertOk();
        }
    }

    public function test_inactive_and_deleted_programmes_are_not_public(): void
    {
        $programme = Programme::first();
        $programme->update(['is_active' => false]);
        $this->get('/programmes/'.$programme->slug)->assertNotFound();
        $this->get('/programmes')->assertDontSee($programme->translated('title'));
        $programme->delete();
        $this->get('/programmes/'.$programme->slug)->assertNotFound();
    }

    public function test_programme_slugs_are_generated_uniquely(): void
    {
        $data = ['title' => ['en' => 'Community Health'], 'short_description' => ['en' => 'Community support']];
        $first = Programme::create($data);
        $second = Programme::create($data);
        $this->assertSame('community-health', $first->slug);
        $this->assertSame('community-health-2', $second->slug);
    }

    public function test_manual_translation_and_english_fallback(): void
    {
        $programme = Programme::first();
        $programme->update(['title' => ['en' => 'Community health', 'sw' => 'Afya ya jamii']]);
        $this->withSession(['locale' => 'sw'])->get('/programmes/'.$programme->slug)->assertOk()->assertSee('Afya ya jamii')->assertSee($programme->short_description['en']);
        $this->post('/language/en')->assertRedirect();
        $this->assertSame('en', session('locale'));
        $this->post('/language/fr')->assertNotFound();
    }

    public function test_settings_control_hero_and_contact_details(): void
    {
        $settings = SiteSetting::first();
        $settings->update(['data' => array_replace($settings->data, ['hero_title' => 'Health starts here', 'email' => 'verified@example.org'])]);
        $this->get('/')->assertOk()->assertSee('Health starts here')->assertSee('verified@example.org');
    }

    public function test_blade_escapes_editorial_content(): void
    {
        $programme = Programme::first();
        $programme->update(['description' => ['en' => '<script>alert(1)</script>']]);
        $this->get('/programmes/'.$programme->slug)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_seeders_are_idempotent_and_do_not_overwrite_edits_or_reset_passwords(): void
    {
        $admin = User::where('email', 'admin@ughi.local')->first();
        $admin->update(['password' => 'A-New-Password!42', 'must_change_password' => false]);
        Programme::first()->update(['title' => ['en' => 'Edited title']]);
        $this->seed();
        $this->assertDatabaseCount('programmes', 7);
        $this->assertDatabaseCount('roles', 10);
        $this->assertTrue(Hash::check('A-New-Password!42', $admin->fresh()->password));
        $this->assertSame('Edited title', Programme::first()->title['en']);
    }

    public function test_admin_login_works_and_invalid_credentials_fail(): void
    {
        $this->get('/admin/login')->assertOk();
        Livewire::test(Login::class)->fillForm(['email' => 'admin@ughi.local', 'password' => 'incorrect'])->call('authenticate')->assertHasFormErrors();
        $this->assertGuest();
        Livewire::test(Login::class)->fillForm(['email' => 'admin@ughi.local', 'password' => 'Admin@1234'])->call('authenticate')->assertHasNoFormErrors();
        $this->assertAuthenticatedAs(User::where('email', 'admin@ughi.local')->first());
    }

    public function test_first_login_requires_password_change(): void
    {
        $admin = User::where('email', 'admin@ughi.local')->first();
        $this->actingAs($admin)->get('/admin')->assertRedirect(route('filament.admin.auth.profile'));
        $this->get('/admin/profile')->assertOk();
        $admin->update(['must_change_password' => false]);
        $this->get('/admin')->assertOk();
    }

    public function test_guest_inactive_and_unprivileged_users_cannot_enter_admin(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create(['must_change_password' => false]))->get('/admin')->assertForbidden();
        $inactive = $this->officer();
        $inactive->update(['is_active' => false]);
        $this->actingAs($inactive)->get('/admin')->assertForbidden();
    }

    public function test_permissions_separate_content_settings_and_safeguarding(): void
    {
        $editor = $this->officer();
        $this->assertTrue($editor->can('programme.update'));
        $this->assertFalse($editor->can('settings.manage'));
        $this->assertFalse($editor->can('safeguarding.view'));
        $this->actingAs($editor)->get('/admin/homepage')->assertOk();
        $this->get('/admin/settings')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $safeguardingOfficer = $this->officer('Safeguarding Officer');
        $this->assertTrue($safeguardingOfficer->can('safeguarding.view'));
        $this->assertFalse($this->officer('Administrator')->can('safeguarding.view'));
    }

    public function test_programme_policy_restricts_write_access(): void
    {
        $officer = $this->officer('Programme Officer');
        $this->assertTrue(Gate::forUser($officer)->allows('update', Programme::first()));
        $this->assertFalse(Gate::forUser($officer)->allows('delete', Programme::first()));
        $this->assertFalse(Gate::forUser($this->officer('Membership Officer'))->allows('viewAny', Programme::class));
    }

    public function test_homepage_cms_save_updates_only_authorized_fields(): void
    {
        $editor = $this->officer();
        $this->actingAs($editor);
        Livewire::test(Homepage::class)->fillForm(['hero_title' => 'Listening to communities'])->set('data.email', 'tampered@example.org')->call('save')->assertHasNoFormErrors();
        $settings = SiteSetting::first()->data;
        $this->assertSame('Listening to communities', $settings['hero_title']);
        $this->assertNull($settings['email']);
        $this->get('/')->assertSee('Listening to communities');
    }

    public function test_cms_rejects_unsafe_cta_urls(): void
    {
        $this->actingAs($this->officer());
        Livewire::test(Homepage::class)->fillForm(['hero_primary_url' => 'javascript:alert(1)'])->call('save')->assertHasFormErrors(['hero_primary_url']);
    }

    public function test_system_settings_are_editable_only_with_permission(): void
    {
        $this->actingAs($this->officer('Administrator'));
        Livewire::test(Settings::class)->fillForm(['email' => 'hello@example.org'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('hello@example.org', SiteSetting::first()->data['email']);
    }

    public function test_super_admin_role_and_user_policies_protect_privilege_boundaries(): void
    {
        $admin = User::where('email', 'admin@ughi.local')->first();
        $other = $this->officer('Administrator');
        $this->assertFalse(Gate::forUser($other)->allows('update', $admin));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $admin));
        $this->assertFalse(Gate::forUser($admin)->allows('update', Role::findByName('Super Admin')));
    }

    public function test_sitemap_and_error_pages_work(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml')->assertSee('/programmes/');
        $this->get('/does-not-exist')->assertNotFound()->assertSee('Return Home');
    }

    public function test_profile_requires_current_password_and_clears_first_login_flag(): void
    {
        $admin = User::where('email', 'admin@ughi.local')->first();
        $this->actingAs($admin);
        Livewire::test(EditProfile::class)
            ->fillForm(['password' => 'New-Strong-Password!42', 'passwordConfirmation' => 'New-Strong-Password!42', 'currentPassword' => 'incorrect'])
            ->call('save')->assertHasFormErrors(['currentPassword']);
        $this->assertTrue($admin->fresh()->must_change_password);
        Livewire::test(EditProfile::class)
            ->fillForm(['password' => 'New-Strong-Password!42', 'passwordConfirmation' => 'New-Strong-Password!42', 'currentPassword' => 'Admin@1234'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertFalse($admin->fresh()->must_change_password);
        $this->assertTrue(Hash::check('New-Strong-Password!42', $admin->fresh()->password));
    }

    public function test_programme_cms_creates_updates_hides_and_restores(): void
    {
        $this->actingAs($this->officer());
        Livewire::test(CreateProgramme::class)
            ->fillForm(['title' => ['en' => 'A community programme'], 'short_description' => ['en' => 'Community support'], 'description' => ['en' => 'Working together.'], 'display_order' => 8, 'icon' => 'heart', 'is_active' => true])
            ->call('create')->assertHasNoFormErrors();
        $programme = Programme::where('slug', 'a-community-programme')->firstOrFail();
        Livewire::test(EditProgramme::class, ['record' => $programme->slug])
            ->fillForm(['title.en' => 'Updated programme', 'is_active' => false])->call('save')->assertHasNoFormErrors();
        $this->assertFalse($programme->fresh()->is_active);
        $this->get('/programmes/'.$programme->slug)->assertNotFound();
        $programme->delete();
        $this->assertSoftDeleted($programme);
        $programme->restore();
        $this->assertNotSoftDeleted($programme);
    }

    public function test_images_are_reencoded_resized_and_given_safe_thumbnail_names(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('user-controlled-name.png', 2400, 1200);
        $path = app(ImageStorage::class)->store($image);
        $this->assertMatchesRegularExpression('/^images\/[a-f0-9-]{36}\.webp$/', $path);
        $disk = Storage::disk('public');
        $disk->assertExists($path);
        $size = getimagesizefromstring($disk->get($path));
        $this->assertSame('image/webp', $size['mime']);
        $this->assertSame(1920, $size[0]);
        foreach ([960, 480] as $maximum) {
            $thumb = ImageStorage::thumbnail($path, $maximum);
            $disk->assertExists($thumb);
            $this->assertSame($maximum, getimagesizefromstring($disk->get($thumb))[0]);
        }
    }

    public function test_image_storage_rejects_files_with_fake_image_extensions(): void
    {
        $this->expectException(ValidationException::class);
        app(ImageStorage::class)->store(UploadedFile::fake()->createWithContent('payload.png', '<?php echo "not an image";'));
    }
}
