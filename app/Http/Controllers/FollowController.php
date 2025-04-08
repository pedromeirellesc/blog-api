<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(Request $request, int $userId)
    {
        $followerId = $request->user()->id;

        $request->merge([
            'follower_id' => (int) $request->user()->id,
            'followed_id' => $userId,
        ]);

        $request->validate([
            'followed_id' => ['required', 'exists:users,id', 'different:follower_id'],
            'follower_id' => ['required', 'exists:users,id', 'different:followed_id'],
        ], [
            'followed_id.different' => 'You cannot follow yourself.',
        ]);

        $exists = Follow::where('followed_id', $userId)
            ->where('follower_id', $followerId)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'You are already following this user.',
            ], 422);
        }

        $follow = Follow::create([
            'follower_id' => $followerId,
            'followed_id' => $userId,
        ]);

        return response()->json([
            'message' => 'Followed successfully.',
            'follow' => $follow,
        ], 200);
    }

    public function unfollow(Request $request, int $userId)
    {

        $follow = Follow::where('follower_id', $request->user()->id)
            ->where('followed_id', $userId)
            ->first();

        if ($follow) {
            $follow->delete();

            return response()->json([
                'message' => 'Unfollowed successfully.',
            ], 200);
        }

        return response()->json([
            'message' => 'Not following this user.',
        ], 404);
    }
}
