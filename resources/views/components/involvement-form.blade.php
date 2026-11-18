@php
    $isVolunteer = $type === 'volunteer';
    $isMembership = $type === 'membership';
    $isPartnership = $type === 'partnership';
@endphp
<div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(280px,.65fr)]">
    <div>
        @if(session('involvement_success'))<div role="status" class="mb-6 rounded-xl border border-primary/20 bg-canvas p-5 text-sm leading-6 text-primary">{{ session('involvement_success') }}</div>@endif
        <h2 class="heading mb-3">{{ $isVolunteer ? 'Volunteer interest form' : ($isMembership ? 'Membership interest form' : 'Partnership enquiry') }}</h2>
        <p class="mb-7 leading-7 text-muted">Share your details and our team will follow up. Please do not include confidential medical, safeguarding, or financial information.</p>
        <form action="{{ route('involvement.store', ['type' => $type]) }}" method="post" class="card grid gap-5 p-6 sm:grid-cols-2 md:p-8">@csrf
            <div><label for="involvement-name" class="text-sm font-semibold">{{ $isPartnership ? 'Contact person' : 'Full name' }}</label><input id="involvement-name" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="160" class="filter-input mt-2">@error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
            <div><label for="involvement-email" class="text-sm font-semibold">Email</label><input id="involvement-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="255" class="filter-input mt-2">@error('email')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
            <div><label for="involvement-phone" class="text-sm font-semibold">Phone <span class="font-normal text-muted">(optional)</span></label><input id="involvement-phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" maxlength="50" class="filter-input mt-2">@error('phone')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
            @if($isPartnership)<div><label for="involvement-organization" class="text-sm font-semibold">Organization</label><input id="involvement-organization" name="organization" value="{{ old('organization') }}" required maxlength="180" class="filter-input mt-2">@error('organization')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>@endif
            @if($isVolunteer || $isMembership)
                <div><label for="involvement-interest" class="text-sm font-semibold">{{ $isVolunteer ? 'Skills or area of interest' : 'Membership interests' }}{{ $isVolunteer ? '' : ' (optional)' }}</label><input id="involvement-interest" name="interest_area" value="{{ old('interest_area') }}" maxlength="180" @if($isVolunteer) required @endif class="filter-input mt-2">@error('interest_area')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
            @endif
            @if($isVolunteer)<div><label for="involvement-availability" class="text-sm font-semibold">Availability <span class="font-normal text-muted">(optional)</span></label><input id="involvement-availability" name="availability" value="{{ old('availability') }}" maxlength="180" placeholder="For example, weekdays or weekends" class="filter-input mt-2">@error('availability')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>@endif
            @if($isPartnership)<div class="sm:col-span-2"><label for="involvement-interest" class="text-sm font-semibold">Area of collaboration <span class="font-normal text-muted">(optional)</span></label><input id="involvement-interest" name="interest_area" value="{{ old('interest_area') }}" maxlength="180" class="filter-input mt-2"></div>@endif
            @if($isPartnership)<div class="sm:col-span-2"><label for="involvement-message" class="text-sm font-semibold">Tell us about the collaboration you have in mind</label><textarea id="involvement-message" name="message" required rows="5" maxlength="5000" class="filter-input mt-2">{{ old('message') }}</textarea>@error('message')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>@endif
            <div class="hidden" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
            <div class="sm:col-span-2"><label class="flex items-start gap-3 text-sm leading-6"><input type="checkbox" name="privacy_consent" value="1" required @checked(old('privacy_consent')) class="mt-1"><span>UGHI may use these details to review this submission and contact me about it. See our <a href="{{ url('/privacy-policy') }}" class="text-link inline">Privacy Policy</a>.</span></label>@error('privacy_consent')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><button type="submit" class="btn btn-primary">{{ $isVolunteer ? 'Send volunteer interest' : ($isMembership ? 'Send membership interest' : 'Send partnership enquiry') }}<x-ughi-icon name="arrow-right" class="h-4 w-4" /></button></div>
        </form>
    </div>
    <aside class="h-fit rounded-2xl bg-canvas p-7"><h2 class="text-lg font-bold">What happens next?</h2><p class="mt-4 text-sm leading-6 text-muted">Your submission goes to the UGHI team for review. A staff member may contact you for more information. Sending an interest form does not guarantee acceptance, membership, or a formal partnership.</p><p class="mt-5 border-t border-line pt-5 text-xs leading-5 text-muted">We use your contact details only to review and respond to this enquiry.</p></aside>
</div>
