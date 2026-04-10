<x-mail::message>
# New Curated Onboarding Application (#{{$application->id}})

A new curated onboarding application has been submitted and is waiting for review.

<x-mail::panel>
**Email:** {{ Str::of($application->email)->mask('*', 6, -5) }}
</x-mail::panel>


<x-mail::table>
|   Detail  |     Value     |
| :---- | --------------- |
| Requested Username | **{{ $application->username_requested ?? 'Not specified' }}** |
| Age at submission | **{{ $application->age_at_submission ?? 'Not specified' }}** |
| Fediverse Account | **{{ $application->fediverse_account ?? 'Not specified' }}** |
| Submission Created  | **{{ $application->created_at->diffForHumans() }}** |
| Email Verified At  | **{{ $application->email_verified_at->diffForHumans() }}** |
</x-mail::table>

<x-mail::button :url="$reviewUrl">
Review Application
</x-mail::button>

@lang('Regards,')<br>
[{{ parse_url(config('app.url'), PHP_URL_HOST) }}]({{config('app.url')}})
</x-mail::message>
