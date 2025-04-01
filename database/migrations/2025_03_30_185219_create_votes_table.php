<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->morphs('votable');
            $table->unsignedBigInteger('user_id');
            $table->enum('vote', ['up', 'down']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['votable_id', 'votable_type', 'user_id'], 'unique_vote');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
