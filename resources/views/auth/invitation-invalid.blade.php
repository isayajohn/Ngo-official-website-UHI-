<x-layout title="Invitation unavailable">
    <section class="mx-auto max-w-xl px-4 py-16 sm:py-24">
        <div class="rounded-3xl border border-border bg-surface p-8 shadow-sm sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">UGHI Account Security</p>
            <h1 class="mt-3 text-3xl font-bold text-text">Invitation unavailable</h1>
            <p class="mt-3 text-muted">This invitation is invalid, has expired, or has already been used. Contact your system administrator for a new invitation.</p>
            <a href="{{ route('filament.admin.auth.login') }}" class="mt-8 inline-flex rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark">Return to Login</a>
        </div>
    </section>
</x-layout>
