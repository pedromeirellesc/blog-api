<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'postId' => $this->post_id,
            'parentId' => $this->parent_id ?? null,
            'content' => $this->content,
            'countVotes' => $this->count_votes,
            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at
        ];
    }
}
