<?php

use App\Enums\RoundStatus;
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
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lobby_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('number');
            $table->string('status')->default(RoundStatus::Voting->value);
            $table->foreignId('question_id')->constrained();
            $table->foreignId('reader_player_id')->constrained('players');
            $table->foreignId('reader_emotion_id')->nullable()->constrained('emotions')->nullOnDelete();
            $table->foreignId('current_interrogation_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('revealed_at')->nullable();
            $table->timestamps();

            $table->unique(['lobby_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_rounds');
    }
};
