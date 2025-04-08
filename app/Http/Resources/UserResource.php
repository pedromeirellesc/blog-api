<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'posts' => PostResource::collection($this->posts),
            'createdAt' => date('d/m/Y H:i:s', strtotime($this->created_at)),
            'updatedAt' => date('d/m/Y H:i:s', strtotime($this->updated_at)),
        ];
    }
}
