<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use App\Services\HashidService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProfileLink extends Model
{
    use HasSnowflakePrimary;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $fillable = [
        'profile_id',
        'url',
        'title',
        'position',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'id' => 'string',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'click_count' => 'integer',
        'position' => 'integer',
    ];

    protected $hidden = [
        'title',
        'position',
        'created_at',
        'updated_at',
        'profile_id',
        'is_verified',
        'verified_at',
        'click_count',
    ];

    public function getVisibleLink()
    {
        $url = $this->url;
        $parts = parse_url($url);
        $display = ($parts['host'] ?? '').($parts['path'] ?? '');

        return Str::limit($display, 24);
    }

    public function getUrl()
    {
        $pid = HashidService::encode((string) $this->profile_id);
        $id = HashidService::encode((string) $this->id);

        return url('/r/pl/'.$pid.'/'.$id);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function incrementClickCount(): void
    {
        $this->increment('click_count');
    }

    public function getDomain(): string
    {
        return parse_url($this->url, PHP_URL_HOST) ?? '';
    }
}
