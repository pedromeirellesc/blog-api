<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteRequest;
use App\Models\Vote;

class VoteController extends Controller
{

    public function vote(StoreVoteRequest $request)
    {
        $voteValidated = $request->validated();

        $request->user()->votes()->create($voteValidated);

        return response()->json([
            'message' => 'Vote recorded succesfully.'
        ], 200);
    }
}
