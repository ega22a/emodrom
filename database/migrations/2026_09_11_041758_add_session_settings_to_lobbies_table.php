<?php

use App\Enums\EmotionSet;
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
        Schema::table('lobbies', function (Blueprint $table) {
            $table->string('host_token');
            $table->foreignId('question_bank_id')->nullable()->constrained();
            $table->unsignedSmallInteger('round_limit')->nullable();
            $table->boolean('interrogation_enabled')->default(false);
            $table->string('emotion_set')->default(EmotionSet::Classic->value);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lobbies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('question_bank_id');
            $table->dropColumn(['host_token', 'round_limit', 'interrogation_enabled', 'emotion_set']);
        });
    }
};
