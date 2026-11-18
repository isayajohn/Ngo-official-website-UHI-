{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach(array_merge(['/', '/about', '/about/departments', '/about/partners', '/about/vision-mission', '/programmes', '/projects', '/impact', '/where-we-work'], array_map(fn($path) => '/'.$path, array_keys(config('ughi_pages')))) as $path)
<url><loc>{{ url($path) }}</loc></url>
@endforeach
@foreach($programmes as $programme)<url><loc>{{ route('programmes.show', $programme) }}</loc><lastmod>{{ $programme->updated_at->toAtomString() }}</lastmod></url>@endforeach
@foreach($projects as $project)<url><loc>{{ route('projects.show', $project) }}</loc><lastmod>{{ $project->updated_at->toAtomString() }}</lastmod></url>@endforeach
@foreach($departments as $department)<url><loc>{{ route('departments.show', $department) }}</loc><lastmod>{{ $department->updated_at->toAtomString() }}</lastmod></url>@endforeach
@foreach($partners as $partner)<url><loc>{{ route('partners.show', $partner) }}</loc><lastmod>{{ $partner->updated_at->toAtomString() }}</lastmod></url>@endforeach
</urlset>
