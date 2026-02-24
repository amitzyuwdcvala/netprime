<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\Services\API\VideoAccessService;
use Illuminate\Http\Request;

class VideoAccessController extends Controller
{
    use ApiResponses;

    public $videoAccessService;

    public function __construct(VideoAccessService $videoAccessService)
    {
        $this->videoAccessService = $videoAccessService;
    }

    /**
     * Handle video access request
     * No request body needed - user identified by Android ID
     */
    public function access(Request $request)
    {
        return $this->videoAccessService->access_video_service($request);
    }
}
