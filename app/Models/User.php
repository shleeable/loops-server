<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'can_upload',
        'can_follow',
        'can_comment',
        'can_like',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'email',
        'password',
        'remember_token',
        'push_token',
        'device',
        'push_token_verified_at',
        'admin_note',
        'two_factor_secret',
        'two_factor_backups',
        'email_verification_token',
        'last_active_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'device' => 'json',
            'push_token_verified_at' => 'datetime',
        ];
    }

    public function active(): bool
    {
        return $this->whereNotNull('email_verified_at')->where('status', 1)->exists();
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'profile_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'profile_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(VideoLike::class, 'profile_id');
    }

    public function followers(): HasMany
    {
        return $this->hasMany(Follower::class, 'following_id', 'profile_id');
    }

    public function following(): HasMany
    {
        return $this->hasMany(Follower::class, 'profile_id', 'profile_id');
    }

    public function videoViews(): HasMany
    {
        return $this->hasMany(UserVideoView::class);
    }

    public function dataExports(): HasMany
    {
        return $this->hasMany(DataExport::class);
    }

    public function dataSettings(): HasOne
    {
        return $this->hasOne(UserDataSettings::class);
    }

    public function getOrCreateDataSettings(): UserDataSettings
    {
        return $this->dataSettings()->firstOrCreate([
            'user_id' => $this->id,
        ]);
    }

    public function getTotalWatchTimeInHours(): int
    {
        return (int) ($this->videoViews()->sum('watch_time_seconds') / 3600);
    }

    public function getTotalDataSize(): string
    {
        $totalBytes = 0;

        $totalBytes += $this->videos()->sum('size_kb') ?? 0;

        if ($totalBytes) {
            $totalBytes = $totalBytes * 1024;
        }

        $commentsSize = $this->comments()->count() * 500;
        $likesSize = $this->likes()->count() * 100;

        $totalBytes += $commentsSize + $likesSize;

        return $this->formatBytes($totalBytes);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < 4; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
