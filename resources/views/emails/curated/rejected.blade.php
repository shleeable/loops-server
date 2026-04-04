<x-mail::message>
# {{ $greeting }}

@if (!empty($bodyLines))
@foreach ($bodyLines as $line)
{{ $line }}
@endforeach
@else
Thank you for your interest in joining our community.
After reviewing your application, we're unable to approve it at this time.
@endif

@if ($reason)
<x-mail::panel>
**Reason:** {{ $reason }}
</x-mail::panel>
@endif

If you have any questions, feel free to [contact us]({{url('/contact')}}).

@lang('Regards,')<br>
[{{ parse_url(config('app.url'), PHP_URL_HOST) }}]({{config('app.url')}})
</x-mail::message>
