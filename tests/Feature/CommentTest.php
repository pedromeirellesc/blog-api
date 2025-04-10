<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected $url = '/api/comments';

    protected $seed = true;

    public function test_create_comment_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->actingAs($user)->json('POST', $this->url, [
            'post_id' => 1,
            'content' => 'This is a comment',
        ]);

        $response->assertStatus(200)->assertJson([
            'message' => 'Comment created successfully.',
            'comment' => [
                'postId' => 1,
                'content' => 'This is a comment',
                'author' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
            ],
        ]);
    }

    public function test_create_comment_in_parent(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->actingAs($user)->json('POST', $this->url, [
            'post_id' => 1,
            'content' => 'This is a comment',
            'parent_id' => 1,
        ]);

        $response->assertStatus(200)->assertJson([
            'message' => 'Comment created successfully.',
            'comment' => [
                'postId' => 1,
                'parentId' => 1,
                'content' => 'This is a comment',
                'author' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
            ],
        ]);
    }

    public function test_create_comment_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->actingAs($user)->json('POST', $this->url, [
            'post_id' => 1,
            'content' => '',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'content' => [
                'The content field is required.',
            ],
        ]);
    }

    public function test_create_comment_invalid_parent_id(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->actingAs($user)->json('POST', $this->url, [
            'post_id' => 1,
            'content' => 'This is a comment',
            'parent_id' => 999,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'parent_id' => [
                'The selected parent id is invalid.',
            ],
        ]);
    }

    public function test_create_comment_invalid_post_id(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->actingAs($user)->json('POST', $this->url, [
            'post_id' => 999,
            'content' => 'This is a comment',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'post_id' => 'The selected post id is invalid.',
        ]);
    }

    public function test_create_comment_unauthenticated(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', $this->url, [
            'post_id' => 1,
            'content' => 'This is a comment',
        ]);

        $response->assertStatus(401)->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_delete_comment_authenticated_and_author(): void
    {
        $user = User::factory()->create();

        $comment = $user->comments()->create([
            'post_id' => 1,
            'content' => 'This is a comment',
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->actingAs($user)->json('DELETE', $this->url . '/' . $comment->id);

        $response->assertStatus(200)->assertExactJson([
            'message' => "Comment #{$comment->id} deleted successfully.",
        ]);
    }

    public function test_delete_comment_authenticated_and_not_author(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $comment = $otherUser->comments()->create([
            'post_id' => 1,
            'content' => 'This is a comment',
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->actingAs($user)->json('DELETE', $this->url . '/' . $comment->id);

        $response->assertStatus(403)->assertJson([
            'message' => 'This action is unauthorized.',
        ]);
    }

    public function test_delete_comment_not_found(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->actingAs($user)->json('DELETE', $this->url . '/999');

        $response->assertStatus(404)->assertJson([
            'status' => 'error',
            'error' => 'Register #999 not found.',
        ]);
    }

    public function test_delete_comment_unauthenticated(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('DELETE', $this->url . '/1');

        $response->assertStatus(401)->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}
