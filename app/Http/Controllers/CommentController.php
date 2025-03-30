<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{

    public function commentsFromPost(int $postId)
    {
        $comments = Comment::where('post_id', '=', $postId)->orderBy('created_at', 'DESC')->paginate('10');

        return response()->json([
            'comments' => CommentResource::collection($comments)
        ], 200);
    }

    public function store(StoreCommentRequest $request)
    {
        $commentValidated = $request->validated();

        $comment = $request->user()->comments()->create($commentValidated);

        return response()->json([
            'message' => 'Comment created successfully.',
            'comment' => new CommentResource($comment)
        ], 200);
    }

    public function destroy(Int $id)
    {

        try {
            $comment = Comment::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response([
                'status' => 'error',
                'error' => "Register #{$id} not found."
            ], 404);
        }

        Gate::authorize('comment-delete', $comment);

        $comment->delete();

        return response()->json([
            'msg' => "Comment #{$comment->id} deleted succesfully."
        ], 200);
    }
}
