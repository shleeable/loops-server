<x-mail::message>
# 🎉 New Loops Update Available!

A new version of Loops is ready for you to install.

@component('mail::panel')
## Version Information

**Current Version:** {{ $versionData['current_version'] }}  
**Latest Version:** {{ $versionData['latest_version'] }}

**Release:** {{ $versionData['release']['name'] }}  
**Published:** {{ \Carbon\Carbon::parse($versionData['release']['published_at'])->format('F j, Y') }}
@endcomponent

@component('mail::button', ['url' => $versionData['release']['url'], 'color' => 'success'])
View Release Notes
@endcomponent

## Update Instructions

1. Optionally backup your database
2. Review the release notes for breaking changes
3. Pull the latest code from the repository
4. Run migrations and clear caches
5. Restart your services

---

**Running Loops {{ $versionData['current_version'] }}**  
You're receiving this because you're an administrator of this Loops instance.

---

Thanks,<br>
Loops
</x-mail::message>
