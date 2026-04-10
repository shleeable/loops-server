<x-mail::message>
# Thanks for applying!

We received your application to join our community.

Please verify your email address to complete your application:

<x-mail::button :url="$verifyUrl">
Verify Email
</x-mail::button>

Once verified, your application will be reviewed by our team. We'll be in touch soon.

@lang('Regards,')<br>
[{{ parse_url(config('app.url'), PHP_URL_HOST) }}]({{config('app.url')}})
</x-mail::message>
