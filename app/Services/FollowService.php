<?php

namespace App\Services;

use App\Models\Follow;
use Illuminate\Http\Request;

class FollowService
{

    public function buildRequest(Request $request, int $userId): Request
    {
        $request->merge([
            'follower_id' => (int) $request->user()->id,
            'followed_id' => (int) $userId,
        ]);

        return $request;
    }

    public function isAlreadyFollowing(int $userId, int $followerId): bool
    {
        return Follow::where('followed_id', $userId)
            ->where('follower_id', $followerId)
            ->exists();
    }
}
