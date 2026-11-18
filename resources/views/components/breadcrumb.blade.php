@props(['title'])
<nav aria-label="Breadcrumb" class="flex gap-3 text-xs text-muted"><a href="/" class="hover:text-primary">Home</a><span aria-hidden="true">/</span><span aria-current="page">{{ $title }}</span></nav>
