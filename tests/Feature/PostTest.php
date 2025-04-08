<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    private $url = '/api/posts';

    protected $seed = true;

    public function test_create_post_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->actingAs($user)->json('POST', $this->url, [
            'title' => 'Test Post',
            'content' => 'This is a test post.',
        ]);

        $response->assertStatus(200)->assertJson([
            'message' => 'Post created successfully.',
            'post' => [
                'title' => 'Test Post',
                'content' => 'This is a test post.',
                'author' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
            ],
        ]);
    }

    public function test_create_post_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->actingAs($user)->json('POST', $this->url, [
            'title' => '',
            'content' => '',
        ]);

        $response->assertStatus(400)->assertJsonValidationErrors([
            'title' => [
                'The title field is required.',
            ],
            'content' => [
                'The content field is required.',
            ],
        ]);
    }

    public function test_create_post_unauthenticated(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', $this->url, [
            'title' => 'Test Post',
            'content' => 'This is a test post.',
        ]);

        $response->assertStatus(401)->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_delete_post_authenticated_and_author(): void
    {
        $user = User::factory()->create();

        $post = $user->posts()->create([
            'title' => 'Test Post',
            'content' => 'This is a test post.',
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->actingAs($user)->json('DELETE', '/api/posts/' . $post->id);

        $response->assertStatus(200)->assertJson([
            'message' => "Post #{$post->id} deleted successfully.",
        ]);
    }

    public function test_delete_post_authenticated_and_not_author(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $post = $otherUser->posts()->create([
            'title' => 'Test Post',
            'content' => 'This is a test post.',
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->actingAs($user)->json('DELETE', '/api/posts/' . $post->id);

        $response->assertStatus(403)->assertJson([
            'message' => 'This action is unauthorized.',
        ]);
    }

    public function test_delete_post_unauthenticated(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('DELETE', '/api/posts/1');

        $response->assertStatus(401)->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_get_post_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/api/posts/1');

        $response->assertStatus(200);
    }

    public function test_get_post_not_found(): void
    {
        $response = $this->get('/api/posts/9999');

        $response->assertStatus(404)->assertExactJson([
            'status' => 'error',
            'error' => 'Register #9999 not found.',
        ]);
    }

    public function test_get_post_unauthenticated(): void
    {
        $response = $this->get('/api/posts/1');

        $response->assertStatus(200);
    }

    public function test_get_post_with_comments_and_comments_children(): void
    {
        $user = User::factory()->create();

        $post = $user->posts()->create([
            'title' => 'Test Post',
            'content' => 'This is a test post.',
        ]);

        $comment = $user->comments()->create([
            'content' => 'This is a test comment.',
            'post_id' => $post->id,
        ]);

        $childComment = $user->comments()->create([
            'content' => 'This is a test child comment.',
            'post_id' => $post->id,
            'parent_id' => $comment->id,
        ]);

        $response = $this->actingAs($user)->get('/api/posts/' . $post->id);

        $response->assertStatus(200)->assertJson([
            'post' => [
                'id' => $post->id,
                'title' => 'Test Post',
                'content' => 'This is a test post.',
                'comments' => [
                    [
                        'id' => $comment->id,
                        'content' => 'This is a test comment.',
                        'children' => [
                            [
                                'id' => $childComment->id,
                                'content' => 'This is a test child comment.',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_get_posts_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get($this->url);

        $response->assertStatus(200);
    }

    public function test_get_posts_unauthenticated(): void
    {
        $response = $this->get($this->url);

        $response->assertStatus(200);
    }
}
