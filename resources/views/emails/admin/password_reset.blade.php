@component('mail::message')
# Your password has been reset

Hi {{ $recipient->name ?? $recipient->username }},

An administrator has reset the password for your {{ $appName }} account.

**Your new password:**

@component('mail::panel')
<span style="font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 16px; letter-spacing: 0.5px;">{{ $password }}</span>
@endcomponent

@if($forceChange)
You'll be asked to choose a new password the next time you sign in.
@else
We recommend changing this to something only you know as soon as you sign in.
@endif

@component('mail::button', ['url' => $loginUrl, 'color' => 'primary'])
Sign in to {{ $appName }}
@endcomponent

If you did not request this change, please contact support immediately.

Thanks,<br>
The {{ $appName }} team

<small style="color: #9ca3af;">For security, all active sessions on your account may have been signed out. You'll need to sign in again on each of your devices.</small>
@endcomponent
