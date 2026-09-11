<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionBank;
use Illuminate\Database\Seeder;

class QuestionBankSeeder extends Seeder
{
    /**
     * Placeholder content for the default bank — replace with the real
     * situations once they're written. Each is {situation, reward in sparks}.
     *
     * @var list<array{situation: string, reward_sparks: int}>
     */
    private const array PLACEHOLDER_QUESTIONS = [
        ['situation' => 'Ты забыл(а) поздравить близкого друга с днём рождения, а он написал первым: «Спасибо, что вспомнил(а)!»', 'reward_sparks' => 4],
        ['situation' => 'На важной встрече у тебя громко заурчал живот прямо во время твоей презентации.', 'reward_sparks' => 3],
        ['situation' => 'Ты нашёл(а) на улице кошелёк с крупной суммой денег и документами владельца.', 'reward_sparks' => 5],
        ['situation' => 'Коллега присвоил себе твою идею на общем собрании, и все её похвалили.', 'reward_sparks' => 5],
        ['situation' => 'Ты случайно отправил(а) сообщение с жалобой на начальника — самому начальнику.', 'reward_sparks' => 6],
        ['situation' => 'Друзья устроили тебе сюрприз-вечеринку, о которой ты совсем не догадывался(ась).', 'reward_sparks' => 3],
        ['situation' => 'Ты опоздал(а) на самолёт на пять минут и наблюдаешь, как он взлетает.', 'reward_sparks' => 4],
        ['situation' => 'Незнакомый человек в очереди сделал тебе искренний комплимент.', 'reward_sparks' => 2],
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $bank = QuestionBank::updateOrCreate(['name' => 'Разные ситуации']);

        foreach (self::PLACEHOLDER_QUESTIONS as $question) {
            Question::firstOrCreate([
                'question_bank_id' => $bank->id,
                'situation' => $question['situation'],
            ], [
                'reward_sparks' => $question['reward_sparks'],
            ]);
        }
    }
}
