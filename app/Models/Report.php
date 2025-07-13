<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Report extends Model
{
    use HasFactory;

    public $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    #[Scope]
    protected function search(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        if (str_starts_with($search, 'video_id:')) {
            $videoId = trim(substr($search, 9));

            return $query->join('videos', 'reports.reported_video_id', '=', 'videos.id')
                ->where('videos.id', $videoId)
                ->select('reports.*');
        }

        if (str_starts_with($search, 'reported_by:')) {
            $reporterId = trim(substr($search, 12));

            return $query->where('reporter_profile_id', $reporterId);
        }

        if (str_starts_with($search, 'reported_profile_id:')) {
            $profileId = trim(substr($search, 20));

            return $query->where('reports.reported_profile_id', $profileId)
                ->orWhereExists(function ($subQuery) use ($profileId) {
                    $subQuery->select(DB::raw(1))
                        ->from('videos')
                        ->whereColumn('videos.id', 'reports.reported_video_id')
                        ->where('videos.profile_id', $profileId);
                });
        }

        return $query->join('profiles', 'reports.reported_profile_id', '=', 'profiles.id')
            ->where('profiles.username', 'like', '%'.$search.'%')
            ->select('reports.*');
    }

    #[Scope]
    protected function filterByStatus(Builder $query, ?string $sort): Builder
    {
        if ($sort === 'all') {
            return $query;
        }

        $adminSeen = $sort === 'closed';

        return $query->whereAdminSeen($adminSeen);
    }

    #[Scope]
    protected function paginated(Builder $query, int $perPage = 10): Builder
    {
        return $query->orderByDesc('id');
    }

    #[Scope]
    protected function againstProfile(Builder $query, int|string $profileId): Builder
    {
        return $query->where('reported_profile_id', $profileId)
            ->orWhereExists(function ($subQuery) use ($profileId) {
                $subQuery->select(DB::raw(1))
                    ->from('videos')
                    ->whereColumn('videos.id', 'reports.reported_video_id')
                    ->where('videos.profile_id', $profileId);
            });
    }

    public static function totalReportsAgainstProfile(int|string $profileId): int
    {
        return self::where('reported_profile_id', $profileId)
            ->orWhereExists(function ($query) use ($profileId) {
                $query->select(DB::raw(1))
                    ->from('videos')
                    ->whereColumn('videos.id', 'reports.reported_video_id')
                    ->where('videos.profile_id', $profileId);
            })
            ->count();
    }

    public function reportEntityType()
    {
        if ($this->reported_profile_id) {
            return 'profile';
        }

        if ($this->reported_video_id) {
            return 'video';
        }

        if ($this->reported_comment_id) {
            return 'comment';
        }

        return 'Undefined Type';
    }

    public function video()
    {
        return $this->hasOne(Video::class, 'id', 'reported_video_id');
    }

    public function adminUrl()
    {
        return url('/admin/reports/show/'.$this->id);
    }
}
