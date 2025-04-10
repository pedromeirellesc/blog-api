<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    private $url = '/api/votes';

    public function test_vote_unauthenticated(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content' => 'application/json',
        ])->json('POST', $this->url, [
            'votable_id' => 1,
            'votable_type' => 'Post',
            'vote' => 1,
        ]);

        $response->assertStatus(401)->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_vote_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', $this->url, [
            'votable_id' => 1,
            'votable_type' => 'post',
            'vote' => 'up',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'message' => 'Vote recorded successfully.',
        ]);
    }

    public function test_vote_invalid_votable_type(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', $this->url, [
            'votable_id' => 1,
            'votable_type' => 'invalid_type',
            'vote' => 'up',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'votable_type' => 'The selected votable type is invalid.',
        ]);
    }

    public function test_remove_vote(): void
    {
        $user = User::factory()->create();

        $user->votes()->create([
            'votable_id' => 1,
            'votable_type' => 'post',
            'vote' => 'up',
        ]);

        $response = $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', $this->url, [
            'votable_id' => 1,
            'votable_type' => 'post',
            'vote' => 'up',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'message' => 'Vote removed successfully.',
        ]);
    }
}
