<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteRequest;

class VoteController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/votes",
     *     summary="Vote on a post or comment",
     *     tags={"Votes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"votable_type", "votable_id", "vote"},
     *             @OA\Property(property="votable_type", type="string", example="post"),
     *             @OA\Property(property="votable_id", type="integer", example=1),
     *             @OA\Property(property="vote", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vote recorded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vote recorded successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="votable_type", type="array",
     *                     @OA\Items(type="string", example="The votable type field is required.")
     *                 ),
     *                 @OA\Property(property="votable_id", type="array",
     *                     @OA\Items(type="string", example="The votable id field is required.")
     *                 ),
     *                 @OA\Property(property="vote", type="array",
     *                     @OA\Items(type="string", example="The vote field is required.")
     *                 )
     *             )
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
     * @OA\Tag(name="Votes", description="Vote operations")
     */
    public function vote(StoreVoteRequest $request)
    {
        $voteValidated = $request->validated();

        $user = $request->user();

        $existingVote = $user->votes()->where([
            'votable_type' => $voteValidated['votable_type'],
            'votable_id' => $voteValidated['votable_id'],
        ])->first();

        if ($existingVote) {

            if ($existingVote->vote->value === $voteValidated['vote']) {
                $existingVote->delete();

                return response()->json([
                    'message' => 'Vote removed successfully.',
                ], 200);
            }
        }

        $user->votes()->updateOrCreate(
            [
                'votable_type' => $voteValidated['votable_type'],
                'votable_id' => $voteValidated['votable_id'],
            ],
            [
                'vote' => $voteValidated['vote'],
            ]
        );

        return response()->json([
            'message' => 'Vote recorded successfully.',
        ], 200);
    }
}
