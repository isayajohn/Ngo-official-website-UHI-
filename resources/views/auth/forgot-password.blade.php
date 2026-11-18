<x-layout title="Forgot your password?">
    <section class="mx-auto max-w-xl px-4 py-16 sm:py-24">
        <div class="rounded-3xl border border-border bg-surface p-8 shadow-sm sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">{{ config('ughi.defaults.organization_name') }}</p>
            <h1 class="mt-3 text-3xl font-bold text-text">Forgot your password?</h1>
            <p class="mt-3 text-muted">Enter your email address and we will send you a secure password reset link.</p>
            <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-text">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="w-full rounded-xl border border-border bg-white px-4 py-3 text-text focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @error('email')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="w-full rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Send Reset Link</button>
            </form>
            <a href="{{ route('filament.admin.auth.login') }}" class="mt-6 inline-flex font-medium text-primary hover:underline">Back to Login</a>
        </div>
    </section>
</x-layout>
