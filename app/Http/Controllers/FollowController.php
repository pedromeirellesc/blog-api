<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Services\FollowService;
use Illuminate\Http\Request;

class FollowController extends Controller
{

    public $followService;
    public function __construct()
    {
        $this->followService = new FollowService();
    }
    /**
     * @OA\Post(
     *     path="/api/follow/{userId}",
     *     summary="Follow a user",
     *     tags={"Follow"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="ID of the user to follow",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Followed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Followed successfully."),
     *             @OA\Property(property="follow", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="follower_id", type="integer", example=1),
     *                 @OA\Property(property="followed_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You are already following this user.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     * @OA\Tag(name="Follow", description="Follow operations")
     */
    public function follow(Request $request, int $userId)
    {
        $followerId = $request->user()->id;

        $request = $this->followService->buildRequest($request, $userId);

        $request->validate([
            'followed_id' => ['required', 'exists:users,id', 'different:follower_id'],
            'follower_id' => ['required', 'exists:users,id', 'different:followed_id'],
        ], [
            'followed_id.different' => 'You cannot follow yourself.',
        ]);

        if ($this->followService->isAlreadyFollowing($userId, $followerId)) {
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

    /**
     * @OA\Delete(
     *     path="/api/unfollow/{userId}",
     *     summary="Unfollow a user",
     *     tags={"Follow"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="ID of the user to unfollow",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unfollowed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unfollowed successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not following this user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not following this user.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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
