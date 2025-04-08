<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_already_following(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create();

        $followedUser->followers()->create([
            'follower_id' => $user->id,
            'followed_id' => $followedUser->id,
        ]);

        $response = $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', '/api/follow/' . $followedUser->id);

        $response->assertStatus(422)->assertJson([
            'message' => 'You are already following this user.',
        ]);
    }

    public function test_follow_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', '/api/follow/1');

        $response->assertStatus(200)->assertJson([
            'message' => 'Followed successfully.',
        ]);
    }

    public function test_follow_unauthenticated(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', '/api/follow/1');

        $response->assertStatus(401)->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_follow_yourself(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', '/api/follow/' . $user->id);

        $response->assertStatus(422)->assertJson([
            'errors' => [
                'followed_id' => [
                    'You cannot follow yourself.',
                ],
            ],
        ]);
    }

    public function test_unfollow_authenticated(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create();

        $followedUser->followers()->create([
            'follower_id' => $user->id,
            'followed_id' => $followedUser->id,
        ]);

        $response = $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', '/api/unfollow/' . $followedUser->id);

        $response->assertStatus(200)->assertJson([
            'message' => 'Unfollowed successfully.',
        ]);
    }

    public function test_unfollow_not_following(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create();

        $response = $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', '/api/unfollow/' . $followedUser->id);

        $response->assertStatus(404)->assertJson([
            'message' => 'Not following this user.',
        ]);
    }

    public function test_unfollow_unauthenticated(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', '/api/unfollow/1');

        $response->assertStatus(401)->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}
