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
        Schema::create('player_emotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('emotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unlocked_in_game_round_id')->nullable()->constrained('game_rounds')->nullOnDelete();
            $table->timestamps();

            $table->unique(['player_id', 'emotion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_emotions');
    }
};
