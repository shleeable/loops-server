<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Models\Report;

class ReportController extends Controller
{
    use ApiHelpers;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(StoreReportRequest $request)
    {
        $pid = $request->user()->profile_id;
        $type = $request->input('type');
        $id = $request->input('id');
        $key = $request->input('key');

        if ($type === 'video') {
            Report::firstOrCreate([
                'reporter_profile_id' => $pid,
                'reported_video_id' => $id,
            ], [
                'report_type' => $key,
            ]);
        } elseif ($type === 'profile') {
            Report::firstOrCreate([
                'reporter_profile_id' => $pid,
                'reported_profile_id' => $id,
            ], [
                'report_type' => $key,
            ]);
        } elseif ($type === 'comment') {
            Report::firstOrCreate([
                'reporter_profile_id' => $pid,
                'reported_comment_id' => $id,
            ], [
                'report_type' => $key,
            ]);
        }

        return $this->success();
    }
}
