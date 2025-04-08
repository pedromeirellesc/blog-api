<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request)
    {
        $commentValidated = $request->validated();

        $comment = $request->user()->comments()->create($commentValidated);

        return response()->json([
            'message' => 'Comment created successfully.',
            'comment' => new CommentResource($comment),
        ], 200);
    }

    public function destroy(int $id)
    {

        try {
            $comment = Comment::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response([
                'status' => 'error',
                'error' => "Register #{$id} not found.",
            ], 404);
        }

        Gate::authorize('comment-delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => "Comment #{$comment->id} deleted successfully.",
        ], 200);
    }
}
