<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = [
            [
                'slug' => 'first-page',
                'name' => 'Primeira Página',
                'description' => 'Leu sua primeira página em um livro digital.',
                'icon' => 'BookOpen',
                'xp_reward' => 50,
                'criteria' => json_encode(['type' => 'pages_read', 'value' => 1]),
            ],
            [
                'slug' => 'bookworm-1',
                'name' => 'Devorador de Livros I',
                'description' => 'Leu um total de 100 páginas.',
                'icon' => 'Library',
                'xp_reward' => 200,
                'criteria' => json_encode(['type' => 'pages_read', 'value' => 100]),
            ],
            [
                'slug' => 'tech-explorer',
                'name' => 'Explorador Tech',
                'description' => 'Terminou seu primeiro livro de programação.',
                'icon' => 'Terminal',
                'xp_reward' => 500,
                'criteria' => json_encode(['type' => 'finished_tech_books', 'value' => 1]),
            ],
            [
                'slug' => 'streak-7',
                'name' => 'Consistência Pura',
                'description' => 'Manteve uma sequência de leitura de 7 dias.',
                'icon' => 'Flame',
                'xp_reward' => 300,
                'criteria' => json_encode(['type' => 'streak_days', 'value' => 7]),
            ],
            [
                'slug' => 'ai-assistant',
                'name' => 'Mestre da IA',
                'description' => 'Fez 50 perguntas para o assistente de IA.',
                'icon' => 'Sparkles',
                'xp_reward' => 250,
                'criteria' => json_encode(['type' => 'ai_queries', 'value' => 50]),
            ],
        ];

        foreach ($achievements as $achievement) {
            \DB::table('achievements')->updateOrInsert(
                ['slug' => $achievement['slug']],
                $achievement
            );
        }
    }
}
