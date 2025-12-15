<x-mail::message>
# New Report #{{ $id }}

A user (**{{$reporterUsername}}**) reported a **{{$entity}}** for **{{$reportType}}**.

<x-mail::button :url="$url">
View Report
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
