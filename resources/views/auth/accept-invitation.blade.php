<x-layout title="Welcome to UGHI">
    <section class="mx-auto max-w-2xl px-4 py-16 sm:py-24">
        <div class="rounded-3xl border border-border bg-surface p-8 shadow-sm sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Ustawi Global Health Initiative</p>
            <h1 class="mt-3 text-3xl font-bold text-text">Welcome to UGHI</h1>
            <p class="mt-3 text-muted">You have been invited to join the UGHI Management System. Set a secure password to activate your account.</p>
            <dl class="mt-7 grid gap-4 rounded-2xl bg-canvas p-5 sm:grid-cols-2">
                <div><dt class="text-sm text-muted">Name</dt><dd class="font-semibold text-text">{{ $invitation->first_name }} {{ $invitation->last_name }}</dd></div>
                <div><dt class="text-sm text-muted">Email</dt><dd class="font-semibold text-text">{{ $invitation->email }}</dd></div>
                <div><dt class="text-sm text-muted">Department</dt><dd class="font-semibold text-text">{{ $invitation->primaryDepartment?->name ?? 'Not assigned' }}</dd></div>
                <div><dt class="text-sm text-muted">Role</dt><dd class="font-semibold text-text">{{ $invitation->role?->name ?? 'Not assigned' }}</dd></div>
            </dl>
            @error('invitation')<p class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $message }}</p>@enderror
            <form method="POST" action="{{ route('invitations.accept.store', [$invitation, $token]) }}" class="mt-7 space-y-5">
                @csrf
                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-text">Create password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required class="w-full rounded-xl border border-border bg-white px-4 py-3 text-text focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @error('password')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    <p class="mt-2 text-sm text-muted">Use at least 12 characters with uppercase and lowercase letters, a number, and a symbol.</p>
                </div>
                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-text">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="w-full rounded-xl border border-border bg-white px-4 py-3 text-text focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <label class="flex items-start gap-3 text-sm text-text"><input type="checkbox" name="terms" value="1" required class="mt-1 rounded border-border text-primary focus:ring-primary"><span>I agree to follow UGHI acceptable use and privacy requirements.</span></label>
                @error('terms')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                <button type="submit" class="w-full rounded-xl bg-primary px-5 py-3 font-semibold text-white hover:bg-primary-dark">Activate Account</button>
            </form>
        </div>
    </section>
</x-layout>
