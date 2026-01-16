<x-mail::message>
# User Joined via Admin Invite

A new user (@<a href="{{$url}}">{{$username}}</a>) joined using the invite ({{ $invite_title }}).


<x-mail::button :url="$invite_admin_url">
View Admin Invite
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
