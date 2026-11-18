<?php

// Synthetic browser fixtures. Refuses every database that is not explicitly a test schema.
use App\Models\District;
use App\Models\ImpactStatistic;
use App\Models\Programme;
use App\Models\Project;
use App\Models\Region;
use App\Models\User;
use App\Support\ImageStorage;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\UploadedFile;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = config('database.connections.'.config('database.default').'.database');
if (! app()->environment(['local', 'testing']) || ! str_ends_with($database, '_test')) {
    fwrite(STDERR, "Refusing browser fixtures outside a local/test database whose name ends in _test.\n");
    exit(1);
}
config(['cache.default' => 'array', 'permission.cache.store' => 'array']);
$programme = Programme::firstOrFail();
$region = Region::firstOrFail();
$district = District::firstOrFail();
$admin = User::where('email', 'admin@ughi.local')->firstOrFail();
// First-login enforcement is tested separately; this disposable fixture can inspect CMS pages.
$admin->update(['must_change_password' => false]);
$image = app(ImageStorage::class)->store(new UploadedFile(public_path('images/community-care.jpg'), 'care.jpg', 'image/jpeg', null, true));
$paths = [$image, ImageStorage::thumbnail($image), ImageStorage::thumbnail($image, 480)];
if (! is_dir(base_path('.qa'))) {
    mkdir(base_path('.qa'), 0700, true);
}
file_put_contents(base_path('.qa/phase2-fixture-media.json'), json_encode($paths));
$project = Project::updateOrCreate(['slug' => 'test-only-community-outreach'], [
    'programme_id' => $programme->id, 'region_id' => $region->id, 'district_id' => $district->id,
    'title' => ['en' => 'TEST ONLY — Community-led outreach'],
    'short_description' => ['en' => 'Synthetic browser-test content, not a UGHI project or activity.'],
    'description' => ['en' => 'This project exists only in the isolated browser-test database.'],
    'challenge' => ['en' => 'Test content for checking readable project details.'],
    'objectives' => ['en' => 'Exercise the published project page.'], 'activities' => ['en' => 'Verify responsive layouts and accessible navigation.'],
    'target_groups' => ['en' => 'Synthetic test fixture.'], 'status' => 'ACTIVE', 'start_date' => today()->subMonths(2),
    'funding_partner' => 'TEST ONLY — Fictional partner label', 'featured_image' => $image, 'image_alt' => 'Illustrative stock healthcare photograph',
    'project_budget' => 987654321, 'people_reached' => 87654321, 'is_featured' => true, 'is_published' => true,
]);
$project->images()->create(['image' => $image, 'alt_text' => 'Illustrative healthcare photograph', 'caption' => 'TEST ONLY — illustrative stock photograph', 'display_order' => 1]);
Project::updateOrCreate(['slug' => 'test-only-youth-health'], [
    'programme_id' => $programme->id, 'title' => ['en' => 'TEST ONLY — Youth health'], 'short_description' => ['en' => 'Synthetic browser test card.'],
    'description' => ['en' => 'Not a real UGHI project.'], 'status' => 'PLANNED', 'is_featured' => true, 'is_published' => true,
]);
foreach ([['TEST ONLY — Organization reach', null, null, 1234], ['TEST ONLY — Programme reach', $programme->id, null, 500], ['TEST ONLY — Project reach', $programme->id, $project->id, 234]] as [$label, $programmeId, $projectId, $value]) {
    $stat = ImpactStatistic::create(['metric' => 'people_reached', 'label' => ['en' => $label], 'programme_id' => $programmeId, 'project_id' => $projectId, 'value' => $value, 'as_of_date' => today()->subDay(), 'verification_source' => 'INTERNAL SYNTHETIC EVIDENCE']);
    $stat->verify($admin);
    $stat->update(['is_active' => true]);
}
echo "Synthetic browser fixtures added to the isolated test schema only.\n";
