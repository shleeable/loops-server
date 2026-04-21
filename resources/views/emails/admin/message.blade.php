@component('mail::message')

{!! nl2br(e($body)) !!}

@if(config('loops.support_email'))
If you have any questions, please reply to this email or reach us at [{{ config('loops.support_email') }}](mailto:{{ config('loops.support_email') }}).
@endif

@if(!str_contains($body, 'Thanks,'))
Thanks,<br>
The {{ $appName }} team
@endif
@endcomponent
