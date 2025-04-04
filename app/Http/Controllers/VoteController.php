<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteRequest;


class VoteController extends Controller
{

    public function vote(StoreVoteRequest $request)
    {
        $voteValidated = $request->validated();

        $user = $voteValidated->user();

        $existingVote = $user->votes()->where([
            'votable_type' => $voteValidated['votable_type'],
            'votable_id' => $voteValidated['votable_id'],
        ])->first();

        if ($existingVote) {

            if ($existingVote->vote === $voteValidated['vote']) {
                $existingVote->delete();

                return response()->json([
                    'message' => 'Vote removed successfully.'
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
            'message' => 'Vote recorded successfully.'
        ], 200);
    }
}
