<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'comments' => CommentResource::collection($this->comments),
            'countVotes' => $this->count_votes,
            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'createdAt' => date('d/m/Y H:i:s', strtotime($this->created_at)),
            'updatedAt' => date('d/m/Y H:i:s', strtotime($this->updated_at)),
        ];
    }
}
