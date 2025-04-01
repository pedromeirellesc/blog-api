<?php

namespace App\Http\Requests;

use App\Enums\VoteType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'votable_type' => ['required', 'string', 'in:post,comment'],
            'votable_id' => ['required', 'integer'],
            'vote' => ['required', Rule::enum(VoteType::class)],
        ];
    }
}
