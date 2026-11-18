<x-layout title="Reset Password">
    <section class="mx-auto max-w-xl px-4 py-16 sm:py-24">
        <div class="rounded-3xl border border-border bg-surface p-8 shadow-sm sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">UGHI Account Security</p>
            <h1 class="mt-3 text-3xl font-bold text-text">Reset Password</h1>
            <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-text">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email', $email) }}" class="w-full rounded-xl border border-border bg-white px-4 py-3 text-text focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @error('email')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-text">New password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required class="w-full rounded-xl border border-border bg-white px-4 py-3 text-text focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @error('password')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-text">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="w-full rounded-xl border border-border bg-white px-4 py-3 text-text focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <button type="submit" class="w-full rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark">Reset Password</button>
            </form>
        </div>
    </section>
</x-layout>
