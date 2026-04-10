<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int|null $min_age
 * @property string|null $guidelines
 * @property array<array-key, mixed>|null $questions
 * @property int $auto_expire_after
 * @property int $auto_expire_unverified_after
 * @property string|null $approval_template
 * @property string|null $rejection_template
 * @property array<array-key, mixed>|null $admin_email_send_to
 * @property bool $send_rejection_email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $rejection_reasons
 * @property-read array $notify_emails
 * @property-read array $questions_list
 * @property-read array $rejection_reasons_list
 * @property-read array $required_question_indices
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings whereAdminEmailSendTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings whereApprovalTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings whereAutoExpireAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings whereGuidelines($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings whereMinAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings whereQuestions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings whereRejectionReasons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings whereRejectionTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings whereSendRejectionEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationSettings whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CuratedApplicationSettings extends Model
{
    protected $table = 'curated_application_settings';

    protected $fillable = [
        'min_age',
        'guidelines',
        'questions',
        'rejection_reasons',
        'auto_expire_after',
        'auto_expire_unverified_after',
        'approval_template',
        'rejection_template',
        'admin_email_send_to',
        'send_rejection_email',
    ];

    protected $casts = [
        'min_age' => 'integer',
        'questions' => 'array',
        'rejection_reasons' => 'array',
        'auto_expire_after' => 'integer',
        'auto_expire_unverified_after' => 'integer',
        'send_rejection_email' => 'boolean',
        'admin_email_send_to' => 'array',
    ];

    const CACHE_KEY = 'loops:admin:curated_onboarding_settings';

    const CACHE_TTL = 3600;

    const QUESTION_TYPE_TEXT = 'text';

    const QUESTION_TYPE_TEXTAREA = 'textarea';

    const QUESTION_TYPE_SELECT = 'select';

    const ALLOWED_QUESTION_TYPES = [
        self::QUESTION_TYPE_TEXT,
        self::QUESTION_TYPE_TEXTAREA,
        self::QUESTION_TYPE_SELECT,
    ];

    public static function current(): self
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::firstOrCreate([], self::defaults());
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function defaults(): array
    {
        return [
            'min_age' => 16,
            'guidelines' => null,
            'questions' => [],
            'rejection_reasons' => [],
            'admin_email_send_to' => [],
            'auto_expire_after' => 30,
            'auto_expire_unverified_after' => 30,
            'approval_template' => null,
            'rejection_template' => null,
            'send_rejection_email' => false,
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }

    public function getQuestionsListAttribute(): array
    {
        return $this->questions ?? [];
    }

    public function getRejectionReasonsListAttribute(): array
    {
        return $this->rejection_reasons ?? [];
    }

    public function getRequiredQuestionIndicesAttribute(): array
    {
        $indices = [];
        foreach ($this->questions_list as $index => $question) {
            if (! empty($question['required'])) {
                $indices[] = $index;
            }
        }

        return $indices;
    }

    public static function normalizeQuestions(array $questions): array
    {
        $normalized = [];
        foreach ($questions as $index => $q) {
            if (empty($q['label']) || ! is_string($q['label'])) {
                continue;
            }
            $type = in_array($q['type'] ?? '', self::ALLOWED_QUESTION_TYPES)
                ? $q['type']
                : self::QUESTION_TYPE_TEXT;
            $entry = [
                'id' => $q['id'] ?? 'q_'.$index.'_'.time(),
                'label' => trim($q['label']),
                'type' => $type,
                'required' => (bool) ($q['required'] ?? false),
            ];
            if ($type === self::QUESTION_TYPE_SELECT) {
                $options = array_values(
                    array_filter(
                        array_map('trim', (array) ($q['options'] ?? [])),
                        fn ($opt) => $opt !== ''
                    )
                );
                $entry['options'] = $options;
            }
            $normalized[] = $entry;
        }

        return $normalized;
    }

    public static function normalizeRejectionReasons(array $reasons): array
    {
        $normalized = [];
        foreach ($reasons as $index => $r) {
            if (empty($r['title']) || ! is_string($r['title'])) {
                continue;
            }
            if (empty($r['reason']) || ! is_string($r['reason'])) {
                continue;
            }
            $normalized[] = [
                'id' => $r['id'] ?? 'rr_'.$index.'_'.time(),
                'title' => trim($r['title']),
                'note' => isset($r['note']) ? trim($r['note']) : null,
                'reason' => trim($r['reason']),
            ];
        }

        return $normalized;
    }

    public function getNotifyEmailsAttribute(): array
    {
        return collect($this->admin_email_send_to ?? [])
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function toPublicArray(): array
    {
        return [
            'min_age' => $this->min_age,
            'guidelines' => $this->guidelines,
            'questions' => collect($this->questions_list)->map(fn ($q) => [
                'id' => $q['id'],
                'label' => $q['label'],
                'type' => $q['type'],
                'required' => $q['required'],
                'options' => $q['options'] ?? [],
            ])->values()->all(),
        ];
    }
}
