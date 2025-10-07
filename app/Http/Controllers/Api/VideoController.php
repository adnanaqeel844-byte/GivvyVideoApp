<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Earning;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    /**
     * Get a list of all videos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $videos = Video::all();
        return response()->json($videos);
    }

    /**
     * Get details of a specific video.
     *
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Video $video)
    {
        return response()->json($video);
    }

    /**
     * Record a video watch and award the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordWatch(Request $request, Video $video)
    {
        $user = Auth::user();
        $rewardAmount = $video->reward_amount;

        // --- Basic Anti-Spam/Anti-Fraud Logic (Can be greatly improved) ---
        // Check if user has already been rewarded for this video recently
        $latestEarning = $user->earnings()
                              ->where('type', 'video_watch')
                              ->where('description', 'LIKE', "%Video ID: {$video->id}%")
                              ->latest()
                              ->first();

        // Example: Only reward if the last watch of this video was more than 5 minutes ago
        if ($latestEarning && $latestEarning->created_at->diffInMinutes(now()) < 5) {
             return response()->json(['message' => 'Already rewarded for this video recently. Try again later.'], 400);
        }
        // --- End of Basic Anti-Spam/Anti-Fraud Logic ---

        // Award user
        $user->current_balance += $rewardAmount;
        $user->save();

        $user->earnings()->create([
            'type' => 'video_watch',
            'amount' => $rewardAmount,
            'description' => "Watched video: {$video->title} (Video ID: {$video->id})",
        ]);

        return response()->json([
            'message' => 'Video watch recorded and reward added.',
            'new_balance' => $user->current_balance
        ]);
    }

    /**
     * Record an ad watch and award the user.
     * This assumes the mobile app has verified the ad watch client-side.
     * More robust solutions involve server-side validation with ad networks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordAdWatch(Request $request)
    {
        $user = Auth::user();
        $adReward = 0.001; // Example ad reward per watch

        // --- Basic Anti-Spam/Anti-Fraud Logic ---
        // Example: Only reward for an ad if the last ad watch was more than 30 seconds ago
        $latestAdEarning = $user->earnings()
                                ->where('type', 'ad_watch')
                                ->latest()
                                ->first();

        if ($latestAdEarning && $latestAdEarning->created_at->diffInSeconds(now()) < 30) {
            return response()->json(['message' => 'Please wait a moment before watching another ad.'], 400);
        }
        // --- End of Basic Anti-Spam/Anti-Fraud Logic ---


        $user->current_balance += $adReward;
        $user->save();

        $user->earnings()->create([
            'type' => 'ad_watch',
            'amount' => $adReward,
            'description' => 'Watched a rewarded ad',
        ]);

        return response()->json([
            'message' => 'Ad watch recorded and reward added.',
            'new_balance' => $user->current_balance
        ]);
    }
}
