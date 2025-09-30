<?php

namespace App\Models;

use App\Events\SettingsUpdated;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property array<array-key, mixed>|null $value
 * @property string $type
 * @property string|null $description
 * @property bool $is_public
 * @property int $version
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $updatedBy
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminSetting whereVersion($value)
 *
 * @mixin \Eloquent
 */
class AdminSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public',
        'version',
        'created_at',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'value' => 'array',
        'is_public' => 'boolean',
        'version' => 'integer',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saved(function ($setting) {
            event(new SettingsUpdated($setting));
        });

        static::deleted(function ($setting) {
            event(new SettingsUpdated($setting));
        });
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $type = 'object', $isPublic = false, $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'is_public' => $isPublic,
                'description' => $description,
                'updated_by' => auth()->id(),
                'version' => (static::where('key', $key)->value('version') ?? 0) + 1,
            ]
        );
    }

    public static function getPublicSettings()
    {
        return static::where('is_public', true)
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function getAllSettings()
    {
        return static::all()
            ->pluck('value', 'key')
            ->toArray();
    }
}
