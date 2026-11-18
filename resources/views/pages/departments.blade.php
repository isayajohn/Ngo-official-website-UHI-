<x-layout title="Departments" description="Meet the internal teams that coordinate UGHI’s work.">
<x-page-hero eyebrow="ABOUT UGHI" title="Departments" description="UGHI’s departments coordinate the people, knowledge and systems that support community health programmes."/>
<section class="section"><div class="wrap">
    @if($departments->isNotEmpty())
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($departments as $department)
                <article class="card flex h-full flex-col p-6">
                    <div class="flex items-start justify-between gap-4"><span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary"><x-ughi-icon :name="$department->icon ?: 'users'" class="h-6 w-6"/></span><span class="text-xs font-bold tracking-[.12em] text-muted">{{ $department->code }}</span></div>
                    <h2 class="mt-6 text-xl font-bold">{{ $department->name }}</h2>
                    @if($department->short_description)<p class="mt-3 text-sm leading-7 text-muted">{{ $department->short_description }}</p>@endif
                    @if($department->head)<p class="mt-5 text-xs text-muted">Department head <span class="font-semibold text-ink">{{ $department->head->name }}</span></p>@endif
                    <a href="{{ route('departments.show', $department) }}" class="text-link mt-auto pt-7">Learn more<x-ughi-icon class="h-4 w-4"/></a>
                </article>
            @endforeach
        </div>
    @else
        <x-empty-state title="Departments are being prepared" description="Internal department information will be published here when it is ready to share."/>
    @endif
</div></section>
</x-layout>
