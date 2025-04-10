<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\VoteService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/posts",
     *     tags={"Posts"},
     *     summary="Get all posts",
     *     description="Returns a list of posts",
     *     operationId="getPosts",
     *     @OA\Response(
     *         response=200,
     *         description="A list of posts",
     *         @OA\JsonContent(
     *             @OA\Property(property="posts", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Post Title"),
     *                     @OA\Property(property="content", type="string", example="Post content goes here."),
     *                     @OA\Property(property="author", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="João"),
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                     @OA\Property(property="count_votes", type="integer", example=10),
     *                     @OA\Property(property="comments", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="content", type="string", example="Comment content goes here."),
     *                             @OA\Property(property="author", type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Maria"),
     *                             ),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                             @OA\Property(property="count_votes", type="integer", example=5),
     *                             @OA\Property(property="children", type="array",
     *                                 @OA\Items(
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="content", type="string", example="Child comment content goes here."),
     *                                     @OA\Property(property="author", type="object",
     *                                         @OA\Property(property="id", type="integer", example=1),
     *                                         @OA\Property(property="name", type="string", example="José"),
     *                                     ),
     *                                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                                     @OA\Property(property="count_votes", type="integer", example=2)
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $posts = Post::orderBy('created_at', 'DESC')->paginate(10);

        return response()->json([
            'posts' => PostResource::collection($posts),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/posts",
     *     tags={"Posts"},
     *     summary="Create a new post",
     *     description="Creates a new post",
     *     operationId="createPost",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string", example="Post Title"),
     *                 @OA\Property(property="content", type="string", example="Post content goes here."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post created successfully."),
     *             @OA\Property(property="post", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Post Title"),
     *                 @OA\Property(property="content", type="string", example="Post content goes here."),
     *                 @OA\Property(property="author", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="João"),
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
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
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="title", type="array",
     *                     @OA\Items(type="string", example="The title field is required.")
     *                 ),
     *                 @OA\Property(property="content", type="array",
     *                     @OA\Items(type="string", example="The content field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     * @OA\Tag(
     *     name="Posts",
     *     description="Operations related to posts"
     * )
     * )
     */
    public function store(StorePostRequest $request)
    {
        $postValidated = $request->validated();

        $post = $request->user()->posts()->create($postValidated);

        return response()->json([
            'message' => 'Post created successfully.',
            'post' => new PostResource($post),
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/posts/{id}",
     *     tags={"Posts"},
     *     summary="Get a post by ID",
     *     description="Returns a single post",
     *     operationId="getPostById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post details",
     *         @OA\JsonContent(
     *             @OA\Property(property="post", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Post Title"),
     *                 @OA\Property(property="content", type="string", example="Post content goes here."),
     *                 @OA\Property(property="author", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="João"),
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="count_votes", type="integer", example=10),
     *                 @OA\Property(property="comments", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="content", type="string", example="Comment content goes here."),
     *                         @OA\Property(property="author", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Maria"),
     *                         ),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                         @OA\Property(property="count_votes", type="integer", example=5),
     *                         @OA\Property(property="children", type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="content", type="string", example="Child comment content goes here."),
     *                                 @OA\Property(property="author", type="object",
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="name", type="string", example="José"),
     *                                 ),
     *                                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                                 @OA\Property(property="count_votes", type="integer", example=2)
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="error", type="string", example="Register #1 not found.")
     *         )
     *     )
     * )
     */
    public function show(int $id)
    {
        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response([
                'status' => 'error',
                'error' => "Register #{$id} not found.",
            ], 404);
        }

        $voteService = new VoteService();

        $post->load(['comments' => function ($query) {
            $query->where('parent_id', null);
            $query->with(['children' => function ($query) {
                $query->orderBy('created_at', 'DESC');
            }]);
            $query->orderBy('created_at', 'DESC');
        }]);
        $post->load('user');
        $post->count_votes = $voteService->getVoteBalance('post', $post->id);
        $post->comments = $post->comments->map(function ($comment) use ($voteService) {
            $comment->count_votes = $voteService->getVoteBalance('comment', $comment->id);
            $comment->children = $comment->children->map(function ($child) use ($voteService) {
                $child->count_votes = $voteService->getVoteBalance('comment', $child->id);
                return $child;
            });

            return $comment;
        });

        return response()->json([
            'post' => new PostResource($post),
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     tags={"Posts"},
     *     summary="Delete a post",
     *     description="Deletes a post by ID",
     *     operationId="deletePost",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post #1 deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *        response=401,
     *        description="Unauthorized",
     *        @OA\JsonContent(
     *           @OA\Property(property="message", type="string", example="Unauthenticated."),
     *        )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="error", type="string", example="Register #1 not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function destroy(int $id)
    {

        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response([
                'status' => 'error',
                'error' => "Register #{$id} not found.",
            ], 404);
        }

        Gate::authorize('post-delete', $post);

        $post->delete();

        return response()->json([
            'message' => "Post #{$post->id} deleted successfully.",
        ], 200);
    }
}
