<?php

namespace App\Http\Controllers;

use App\Models\EditorialItem;
use App\Models\ContentPage;
use Illuminate\Support\Facades\Storage;

class EditorialController extends Controller
{
    private const PAGES = [
        'news' => ['News', 'Updates from UGHI and our communities.', 'News and updates', 'News from UGHI will appear here as it is published.'],
        'events' => ['Events', 'Coming together for community health.', 'Upcoming events', 'UGHI events will appear here as they are announced.'],
        'resources/publications' => ['Publications', 'Research and learning for better health.', 'Knowledge worth sharing', 'UGHI publications and resources will appear here when published.'],
        'gallery' => ['Gallery', 'People, communities and shared moments.', 'Our story in pictures', 'Approved photos and albums will appear here as they are published.'],
    ];

    public function index(string $path)
    {
        abort_unless(isset(self::PAGES[$path]), 404);
        [$title, $description, $emptyTitle, $emptyDescription] = self::PAGES[$path];
        $type = $this->typeFor($path);
        $items = EditorialItem::published()->ofType($type)->orderByDesc($type === 'event' ? 'event_starts_at' : 'published_at')->orderByDesc('id')->paginate(12);

        if ($path === 'news' && $items->total() === 0 && ContentPage::where('path', $path)->exists()) {
            return app(InformationController::class)($path);
        }

        return view('pages.editorial-index', compact('title', 'description', 'emptyTitle', 'emptyDescription', 'items', 'type'));
    }

    public function show(string $path, string $slug)
    {
        abort_unless(isset(self::PAGES[$path]) && $path !== 'gallery', 404);
        $item = EditorialItem::published()->ofType($this->typeFor($path))->where('slug', $slug)->firstOrFail();
        $image = $item->image ? Storage::disk('public')->url($item->image) : null;

        return view('pages.editorial-show', compact('item', 'image'));
    }

    private function typeFor(string $path): string
    {
        return match ($path) {
            'news' => 'news',
            'events' => 'event',
            'resources/publications' => 'publication',
            'gallery' => 'gallery',
        };
    }
}
