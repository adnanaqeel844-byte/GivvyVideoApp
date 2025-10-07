<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('thumbnail_url')->nullable();
            $table->string('video_url'); // Actual video stream URL (e.g., YouTube embed URL, direct video file URL)
            $table->string('category')->nullable();
            $table->string('genre')->nullable();
            $table->decimal('reward_amount', 8, 4)->default(0.0000); // Reward for watching this video
            $table->integer('duration_seconds')->nullable(); // Optional: duration for client-side tracking
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
