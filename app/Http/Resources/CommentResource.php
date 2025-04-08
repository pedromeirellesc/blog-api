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
            'children' => CommentResource::collection($this->children),
            'createdAt' => date('d/m/Y H:i:s', strtotime($this->created_at)),
            'updatedAt' => date('d/m/Y H:i:s', strtotime($this->updated_at)),
        ];
    }
}
