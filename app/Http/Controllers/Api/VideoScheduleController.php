<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VideoScheduledResource;
use App\Models\Video;
use App\Services\VideoScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VideoScheduleController extends Controller
{
    public function __construct(protected VideoScheduleService $service)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $videos = Video::where('profile_id', $request->user()->profile_id)
            ->where('publish_state', Video::PUBLISH_STATE_SCHEDULED)
            ->where('status', '!=', VideoScheduleService::STATUS_LIVE)
            ->orderBy('scheduled_at')
            ->cursorPaginate(12);

        return VideoScheduledResource::collection($videos);
    }

    public function pendingCount(Request $request)
    {
        $count = app(VideoScheduleService::class)->pendingCount($request->user()->profile);

        return response()->json([
            'max_allowed' => VideoScheduleService::MAX_PENDING,
            'pending_count' => $count,
            'can_schedule' => $count < VideoScheduleService::MAX_PENDING,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'scheduled_at' => 'required|date',
        ]);

        $video = $this->authorizedVideo($request, $id);

        $this->assertMutable($video);

        $video = $this->service->schedule(
            $video,
            Carbon::parse($request->input('scheduled_at'))->utc()
        );

        return new VideoScheduledResource($video);
    }

    public function publishNow(Request $request, $id)
    {
        $video = $this->authorizedVideo($request, $id);

        $this->assertMutable($video);

        abort_if(
            $video->publish_state === Video::PUBLISH_STATE_FAILED,
            422,
            'This video failed to process and cannot be published.'
        );

        abort_if(
            ! $video->has_processed,
            422,
            'This video is still processing. Try again once it finishes.'
        );

        $video = $this->service->publishNow($video);

        return new VideoScheduledResource($video);
    }

    public function destroy(Request $request, $id)
    {
        $video = $this->authorizedVideo($request, $id);

        $this->assertMutable($video);

        $this->service->deleteScheduled($video);

        return response()->json(['success' => true]);
    }

    protected function authorizedVideo(Request $request, $id): Video
    {
        $video = Video::findOrFail($id);

        abort_if($video->profile_id != $request->user()->profile_id, 403);

        return $video;
    }

    protected function assertMutable(Video $video): void
    {
        abort_if($video->isLive(), 422, 'This video is already published.');

        abort_if(
            $video->publish_state !== Video::PUBLISH_STATE_SCHEDULED
                && $video->publish_state !== Video::PUBLISH_STATE_FAILED,
            422,
            'This video is not scheduled.'
        );

        abort_if(
            (bool) $video->publishing_at,
            409,
            'This video is publishing right now and can no longer be changed.'
        );
    }
}
