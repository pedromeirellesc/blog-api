<?php

namespace App\Services;

use App\Enums\VoteType;
use App\Models\Vote;

class VoteService
{
    public function getVoteBalance(string $votableType, int $votableId): int
    {
        $upVotes = $this->getVotes($votableType, $votableId, VoteType::UP);
        $downVotes = $this->getVotes($votableType, $votableId, VoteType::DOWN);

        return $upVotes - $downVotes;
    }

    public function getVotes(string $votableType, int $votableId, VoteType $voteType): int
    {
        return Vote::where('votable_type', $votableType)
            ->where('votable_id', $votableId)
            ->where('vote', $voteType->value)
            ->count();
    }
}
