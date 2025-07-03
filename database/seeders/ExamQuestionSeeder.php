<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Exams;
use App\Models\Question;
use App\Models\Answer;

class ExamQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first exam
        $exam = Exams::first();
        
        if (!$exam) {
            $this->command->info('No exams found. Please create an exam first.');
            return;
        }

        // Update exam duration
        $exam->update(['duration' => 30]);

        // Create sample questions
        $questions = [
            [
                'question' => 'Qu\'est-ce que React?',
                'type' => 'unique_choice',
                'answers' => [
                    ['answer' => 'Un framework JavaScript', 'is_correct' => false],
                    ['answer' => 'Une bibliothèque JavaScript', 'is_correct' => true],
                    ['answer' => 'Un langage de programmation', 'is_correct' => false],
                    ['answer' => 'Un système de base de données', 'is_correct' => false],
                ]
            ],
            [
                'question' => 'Quel hook React est utilisé pour gérer l\'état local?',
                'type' => 'unique_choice',
                'answers' => [
                    ['answer' => 'useEffect', 'is_correct' => false],
                    ['answer' => 'useState', 'is_correct' => true],
                    ['answer' => 'useContext', 'is_correct' => false],
                    ['answer' => 'useReducer', 'is_correct' => false],
                ]
            ],
            [
                'question' => 'Qu\'est-ce qu\'un composant React?',
                'type' => 'unique_choice',
                'answers' => [
                    ['answer' => 'Une fonction ou classe qui retourne du JSX', 'is_correct' => true],
                    ['answer' => 'Un fichier CSS', 'is_correct' => false],
                    ['answer' => 'Une base de données', 'is_correct' => false],
                    ['answer' => 'Un serveur web', 'is_correct' => false],
                ]
            ],
            [
                'question' => 'Quels sont les avantages de React? (Sélectionnez tous ceux qui s\'appliquent)',
                'type' => 'multiple_choice',
                'answers' => [
                    ['answer' => 'Composants réutilisables', 'is_correct' => true],
                    ['answer' => 'Virtual DOM', 'is_correct' => true],
                    ['answer' => 'Unidirectionnalité des données', 'is_correct' => true],
                    ['answer' => 'Gestion automatique de la base de données', 'is_correct' => false],
                ]
            ],
            [
                'question' => 'Quels hooks React sont utilisés pour les effets secondaires?',
                'type' => 'multiple_choice',
                'answers' => [
                    ['answer' => 'useEffect', 'is_correct' => true],
                    ['answer' => 'useLayoutEffect', 'is_correct' => true],
                    ['answer' => 'useState', 'is_correct' => false],
                    ['answer' => 'useMemo', 'is_correct' => false],
                ]
            ]
        ];

        foreach ($questions as $questionData) {
            $question = Question::create([
                'exams_id' => $exam->id,
                'question' => $questionData['question'],
                'type' => $questionData['type']
            ]);

            foreach ($questionData['answers'] as $answerData) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer' => $answerData['answer'],
                    'is_correct' => $answerData['is_correct']
                ]);
            }
        }

        $this->command->info('Sample exam questions created successfully!');
    }
}
