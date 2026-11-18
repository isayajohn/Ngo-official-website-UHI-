<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\EditorialController;
use App\Http\Controllers\InvolvementApplicationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ImpactController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PartnerController;
use App\Models\Programme;
use App\Models\Project;
use App\Models\Department;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentTaskController;
use App\Http\Controllers\MyWorkApprovalController;
use App\Http\Controllers\AccountRecoveryController;
use App\Http\Controllers\UserInvitationController;

Route::middleware('guest')->group(function (): void {
    Route::get('/forgot-password', [AccountRecoveryController::class, 'forgot'])->name('password.request');
    Route::post('/forgot-password', [AccountRecoveryController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::get('/forgot-password/sent', [AccountRecoveryController::class, 'emailSent'])->name('password.email.sent');
    Route::get('/reset-password/{token}', [AccountRecoveryController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [AccountRecoveryController::class, 'updatePassword'])
        ->middleware('throttle:5,1')->name('password.update');

    Route::get('/invitation/accept/{invitation}/{token}', [UserInvitationController::class, 'show'])->name('invitations.accept.show');
    Route::post('/invitation/accept/{invitation}/{token}', [UserInvitationController::class, 'accept'])
        ->middleware('throttle:10,1')->name('invitations.accept.store');
});

Route::get('/', HomeController::class)->name('home');

Route::get('/profile', fn () => redirect()->route('filament.admin.auth.profile'))->middleware('auth')->name('profile');

Route::middleware('auth')->prefix('admin')->group(function (): void {
    Route::post('/my-work/approvals/{approvalRequest}', MyWorkApprovalController::class)
        ->middleware('can:admin.access')->name('my-work.approval-action');
    Route::post('/department-tasks/{task}/complete', [DepartmentTaskController::class, 'complete'])->name('department-tasks.complete');
    Route::post('/department-tasks/{task}/comments', [DepartmentTaskController::class, 'comment'])->name('department-tasks.comments');
    Route::get('/department-task-attachments/{attachment}', [DepartmentTaskController::class, 'attachment'])->name('department-task-attachments.download');
});
Route::get('/projects', [ProjectController::class, 'index'])->name('projects');
Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/impact', ImpactController::class)->name('impact');
Route::get('/where-we-work', LocationController::class)->name('where-we-work');
Route::get('/about/departments', [DepartmentController::class, 'index'])->name('departments.index');
Route::get('/about/departments/{department:slug}', [DepartmentController::class, 'show'])->name('departments.show');
Route::get('/about/partners', [PartnerController::class, 'index'])->name('partners.index');
Route::get('/about/partners/{partner:slug}', [PartnerController::class, 'show'])->name('partners.show');
Route::post('/contact', [ContactMessageController::class, 'store'])->middleware('throttle:10,1')->name('contact.store');
Route::get('/donate', [DonationController::class, 'index'])->name('donate');
Route::post('/donate', [DonationController::class, 'store'])->middleware('throttle:5,1')->name('donate.store');
Route::post('/get-involved/{type}', [InvolvementApplicationController::class, 'store'])->whereIn('type', ['volunteer', 'membership', 'partnership'])->middleware('throttle:5,1')->name('involvement.store');
Route::get('/news', [EditorialController::class, 'index'])->defaults('path', 'news')->name('news.index');
Route::get('/news/{slug}', [EditorialController::class, 'show'])->defaults('path', 'news')->name('news.show');
Route::get('/events', [EditorialController::class, 'index'])->defaults('path', 'events')->name('events.index');
Route::get('/events/{slug}', [EditorialController::class, 'show'])->defaults('path', 'events')->name('events.show');
Route::get('/resources/publications', [EditorialController::class, 'index'])->defaults('path', 'resources/publications')->name('publications.index');
Route::get('/resources/publications/{slug}', [EditorialController::class, 'show'])->defaults('path', 'resources/publications')->name('publications.show');
Route::get('/gallery', [EditorialController::class, 'index'])->defaults('path', 'gallery')->name('gallery.index');

Route::view('/about', 'pages.about')->name('about');
Route::view('/about/vision-mission', 'pages.about')->name('vision-mission');
Route::get('/programmes', [ProgrammeController::class, 'index'])->name('programmes.index');
Route::get('/programmes/{programme:slug}', [ProgrammeController::class, 'show'])->name('programmes.show');
Route::post('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'sw']), 404);
    session(['locale' => $locale]);

    return back();
})->middleware('throttle:30,1')->name('locale');
Route::get('/sitemap.xml', function () {
    return response()->view('pages.sitemap', ['programmes' => Programme::published()->get(), 'projects' => Project::published()->orderBy('id')->cursor(), 'departments' => Department::published()->get(), 'partners' => \App\Models\Partner::published()->get()])->header('Content-Type', 'application/xml');
})->name('sitemap');
// News is an editorial listing; its controller handles the legacy content-page fallback.
foreach (array_diff(array_keys(config('ughi_pages')), ['news']) as $path) {
    Route::get('/'.$path, InformationController::class)->defaults('path', $path)->name(str_replace('/', '.', $path));
}
