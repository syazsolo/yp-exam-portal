<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Option;
use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Lecturer
        $lecturer = User::factory()->create([
            'name' => 'M. Syazani',
            'email' => 'lecturer@exam.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Lecturer,
        ]);

        // Subjects
        $maths = Subject::create(['name' => 'Mathematics', 'description' => 'Core mathematics', 'created_by' => $lecturer->id]);
        $physics = Subject::create(['name' => 'Physics', 'description' => 'Applied physics', 'created_by' => $lecturer->id]);
        $english = Subject::create(['name' => 'English', 'description' => 'English language', 'created_by' => $lecturer->id]);
        $history = Subject::create(['name' => 'History', 'description' => 'World history', 'created_by' => $lecturer->id]);
        $cs = Subject::create(['name' => 'Computer Science', 'description' => 'Programming and algorithms', 'created_by' => $lecturer->id]);

        // Classes
        $classA = SchoolClass::create(['name' => 'Class A', 'created_by' => $lecturer->id]);
        $classB = SchoolClass::create(['name' => 'Class B', 'created_by' => $lecturer->id]);
        $classC = SchoolClass::create(['name' => 'Class C', 'created_by' => $lecturer->id]);
        $classD = SchoolClass::create(['name' => 'Class D', 'created_by' => $lecturer->id]);

        // Attach subjects to classes
        $classA->subjects()->attach([$maths->id, $physics->id]);
        $classB->subjects()->attach([$english->id, $history->id]);
        $classC->subjects()->attach([$cs->id, $maths->id]);
        $classD->subjects()->attach([$history->id, $english->id]);

        // Students
        $studentsA = User::factory(18)->create(['role' => UserRole::Student, 'password' => Hash::make('password')]);
        $studentsB = User::factory(22)->create(['role' => UserRole::Student, 'password' => Hash::make('password')]);
        $studentsC = User::factory(19)->create(['role' => UserRole::Student, 'password' => Hash::make('password')]);
        $studentsD = User::factory(15)->create(['role' => UserRole::Student, 'password' => Hash::make('password')]);

        $classA->students()->attach($studentsA->pluck('id'));
        $classB->students()->attach($studentsB->pluck('id'));
        $classC->students()->attach($studentsC->pluck('id'));
        $classD->students()->attach($studentsD->pluck('id'));

        // Exams
        $calculus = Exam::create([
            'title' => 'Introduction to Calculus',
            'subject_id' => $maths->id,
            'created_by' => $lecturer->id,
            'time_limit_mins' => 45,
            'status' => 'active',
            'starts_at' => now()->subMinutes(10),
            'ends_at' => now()->addMinutes(35),
        ]);

        $grammar = Exam::create([
            'title' => 'English Grammar Midterm',
            'subject_id' => $english->id,
            'created_by' => $lecturer->id,
            'time_limit_mins' => 30,
            'status' => 'active',
            'starts_at' => now()->subMinutes(5),
            'ends_at' => now()->addMinutes(25),
        ]);

        $dsQuiz = Exam::create([
            'title' => 'Data Structures Quiz',
            'subject_id' => $cs->id,
            'created_by' => $lecturer->id,
            'time_limit_mins' => 20,
            'status' => 'closed',
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->subHours(1)->addMinutes(40),
        ]);

        $physicsFinal = Exam::create([
            'title' => 'Physics Final Exam',
            'subject_id' => $physics->id,
            'created_by' => $lecturer->id,
            'time_limit_mins' => 90,
            'status' => 'draft',
            'starts_at' => now()->addDays(3),
            'ends_at' => now()->addDays(3)->addMinutes(90),
        ]);

        $historyMid = Exam::create([
            'title' => 'History Midterm',
            'subject_id' => $history->id,
            'created_by' => $lecturer->id,
            'time_limit_mins' => 60,
            'status' => 'draft',
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(5)->addMinutes(60),
        ]);

        // Seed questions for Calculus (mix MCQ + open text)
        $this->seedExamQuestions($calculus, mcqCount: 4, openCount: 1);
        $this->seedExamQuestions($grammar, mcqCount: 5, openCount: 0);
        $this->seedExamQuestions($dsQuiz, mcqCount: 3, openCount: 2);
        $this->seedExamQuestions($physicsFinal, mcqCount: 6, openCount: 2);
        $this->seedExamQuestions($historyMid, mcqCount: 4, openCount: 1);

        // Seed 14 sessions for Calculus (Class A students)
        $calculusStudents = $studentsA->take(14);
        foreach ($calculusStudents as $student) {
            ExamSession::create([
                'exam_id' => $calculus->id,
                'user_id' => $student->id,
                'status' => 'in_progress',
                'started_at' => now()->subMinutes(rand(1, 10)),
            ]);
        }

        // Seed 9 sessions for Grammar (Class B students)
        $grammarStudents = $studentsB->take(9);
        foreach ($grammarStudents as $student) {
            ExamSession::create([
                'exam_id' => $grammar->id,
                'user_id' => $student->id,
                'status' => 'in_progress',
                'started_at' => now()->subMinutes(rand(1, 5)),
            ]);
        }
    }

    private function seedExamQuestions(Exam $exam, int $mcqCount, int $openCount): void
    {
        $order = 1;

        for ($i = 0; $i < $mcqCount; $i++) {
            $question = Question::create([
                'exam_id' => $exam->id,
                'type' => 'mcq',
                'body' => fake()->sentence().'?',
                'order' => $order++,
                'points' => 2,
            ]);

            // 4 options, one correct
            $correctIndex = rand(0, 3);
            for ($j = 0; $j < 4; $j++) {
                Option::create([
                    'question_id' => $question->id,
                    'body' => fake()->sentence(),
                    'is_correct' => $j === $correctIndex,
                    'order' => $j + 1,
                ]);
            }
        }

        for ($i = 0; $i < $openCount; $i++) {
            Question::create([
                'exam_id' => $exam->id,
                'type' => 'open_text',
                'body' => fake()->sentence().' Explain in detail.',
                'order' => $order++,
                'points' => 5,
            ]);
        }
    }
}
