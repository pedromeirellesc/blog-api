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
    public function index()
    {
        $posts = Post::with('comments')->orderBy('created_at', 'DESC')->paginate(10);

        return response()->json([
            'posts' => PostResource::collection($posts)
        ]);
    }

    public function store(StorePostRequest $request)
    {
        $postValidated = $request->validated();

        $post = $postValidated->user()->posts()->create($postValidated);

        return response()->json([
            'message' => 'Post created successfully.',
            'post' => new PostResource($post)
        ], 200);
    }

    public function show(int $id)
    {
        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response([
                'status' => 'error',
                'error' => "Register #{$id} not found."
            ], 404);
        }

        $voteService = new VoteService();

        $post->load(['comments' => function ($query) {
            $query->orderBy('created_at', 'DESC');
        }]);
        $post->load('user');
        $post->count_votes = $voteService->getVoteBalance('post', $post->id);
        $post->comments = $post->comments->map(function ($comment) use ($voteService) {
            $comment->count_votes = $voteService->getVoteBalance('comment', $comment->id);
            return $comment;
        });

        return response()->json([
            'post' => new PostResource($post)
        ], 200);
    }

    public function destroy(int $id)
    {

        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response([
                'status' => 'error',
                'error' => "Register #{$id} not found."
            ], 404);
        }

        Gate::authorize('delete-post', $post);

        $post->delete();

        return response()->json([
            'msg' => "Post #{$post->id} deleted succesfully."
        ], 200);
    }
}
