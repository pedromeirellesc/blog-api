<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/comments",
     *     summary="Create a new comment",
     *     tags={"Comments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content", "post_id"},
     *             @OA\Property(property="content", type="string", example="This is a comment."),
     *             @OA\Property(property="post_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comment created successfully."),
     *             @OA\Property(property="comment", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="content", type="string", example="This is a comment."),
     *                 @OA\Property(property="post_id", type="integer", example=1),
     *                 @OA\Property(property="author", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="João"),
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="content", type="array",
     *                     @OA\Items(type="string", example="The content field is required.")
     *                 ),
     *                 @OA\Property(property="post_id", type="array",
     *                     @OA\Items(type="string", example="The post id field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *        response=401,
     *        description="Unauthorized",
     *        @OA\JsonContent(
     *           @OA\Property(property="message", type="string", example="Unauthenticated."),
     *        )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     * @OA\Tag(name="Comments", description="Comment operations")
     */
    public function store(StoreCommentRequest $request)
    {
        $commentValidated = $request->validated();

        $comment = $request->user()->comments()->create($commentValidated);

        return response()->json([
            'message' => 'Comment created successfully.',
            'comment' => new CommentResource($comment),
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/comments/{id}",
     *     summary="Delete a comment",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the comment to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comment #1 deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="error", type="string", example="This action is unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="error", type="string", example="Register #1 not found.")
     *         )
     *     ),
     *    security={{"bearerAuth": {}}}
     * )
     */
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
