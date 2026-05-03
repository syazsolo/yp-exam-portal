<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Option;
use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DemoAcademicPortalSeeder extends Seeder
{
    private const PASSWORD = 'qwertyuiop';

    public function run(): void
    {
        $users = $this->seedUsers();
        $subjects = $this->seedSubjects($users['lecturers']);
        $classes = $this->seedClasses($users['admin'], $subjects);
        $this->enrollStudents($classes, $users['students']);

        $exams = $this->seedExams($subjects);
        $this->seedAttempts($exams, $users['students']);
    }

    /**
     * @return array{admin: User, lecturers: Collection<string, User>, students: Collection<string, User>}
     */
    private function seedUsers(): array
    {
        $password = Hash::make(self::PASSWORD);

        $admin = $this->seedUser('Principal Skinner', 'admin@gmail.com', UserRole::Admin, $password);

        $lecturers = collect([
            ['name' => 'Ms. Frizzle', 'email' => 'lecturer1@gmail.com'],
            ['name' => 'Severus Snape', 'email' => 'lecturer2@gmail.com'],
            ['name' => 'Walter White', 'email' => 'lecturer3@gmail.com'],
        ])->mapWithKeys(fn (array $lecturer) => [
            $lecturer['email'] => $this->seedUser($lecturer['name'], $lecturer['email'], UserRole::Lecturer, $password),
        ]);

        $studentNames = [
            'Jimmy Neutron',
            'Lisa Simpson',
            'Dora Marquez',
            'Ben Tennyson',
            'Kim Possible',
            'Phineas Flynn',
            'Ferb Fletcher',
            'Wednesday Addams',
            'Matilda Wormwood',
            'Harry Potter',
            'Hermione Granger',
            'Ron Weasley',
            'Katara',
            'Aang',
            'Miles Morales',
        ];
        $classIds = ['ROB-AUTO-2017', 'AI-APPS-2020', 'ENG-FOUNDATION', 'DATA-SYSTEMS-2024', 'EMBEDDED-CLOUD-2025'];

        $students = collect(range(1, 15))->mapWithKeys(function (int $number) use ($password, $studentNames, $classIds) {
            $student = [
                'name' => $studentNames[$number - 1],
                'email' => "student{$number}@gmail.com",
                'class_id' => $classIds[($number - 1) % count($classIds)],
            ];
            $user = $this->seedUser($student['name'], $student['email'], UserRole::Student, $password);
            $user->setAttribute('seed_class_id', $student['class_id']);

            return [$student['email'] => $user];
        });

        return compact('admin', 'lecturers', 'students');
    }

    private function seedUser(string $name, string $email, UserRole $role, string $password): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'role' => $role,
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );
    }

    /**
     * Transcript-inspired subjects from docs/academic_transcript.pdf.
     *
     * @return Collection<string, Subject>
     */
    private function seedSubjects(Collection $lecturers): Collection
    {
        return collect([
            ['id' => 'ECP1016', 'lecturer' => 1, 'name' => 'Computer and Program Design', 'description' => 'Introductory programming, problem decomposition, control flow, and practical debugging.'],
            ['id' => 'ECT1016', 'lecturer' => 2, 'name' => 'Circuit Theory', 'description' => 'DC/AC circuit analysis, network theorems, and electronics fundamentals.'],
            ['id' => 'EEN1016', 'lecturer' => 3, 'name' => 'Engineering Mathematics I', 'description' => 'Calculus, vectors, and applied engineering problem solving.'],
            ['id' => 'ECP1026', 'lecturer' => 1, 'name' => 'Algorithms and Data Structures', 'description' => 'Complexity, arrays, linked structures, trees, graphs, sorting, and searching.'],
            ['id' => 'ECT3016', 'lecturer' => 2, 'name' => 'Power Technology', 'description' => 'Power systems, machines, transformers, and electrical safety.'],
            ['id' => 'ERT2016', 'lecturer' => 3, 'name' => 'Engineering Mechanics', 'description' => 'Statics, dynamics, force systems, and mechanical design calculations.'],
            ['id' => 'ECP3086', 'lecturer' => 1, 'name' => 'Multimedia Technology and Applications', 'description' => 'Media pipelines, interface design, compression, and interactive applications.'],
            ['id' => 'ERT3026', 'lecturer' => 2, 'name' => 'Automation', 'description' => 'Sensors, actuators, PLC thinking, and industrial automation workflows.'],
            ['id' => 'ERT3016', 'lecturer' => 3, 'name' => 'Robotics', 'description' => 'Robot kinematics, motion planning, control loops, and embedded integration.'],
            ['id' => 'ECP3266', 'lecturer' => 1, 'name' => 'Artificial Intelligence and Applications', 'description' => 'Search, reasoning, machine learning basics, and applied intelligent systems.'],
            ['id' => 'ERT3036', 'lecturer' => 2, 'name' => 'Advanced Robotics', 'description' => 'Robot perception, localization, trajectory planning, and autonomous behavior.'],
            ['id' => 'ECP4256', 'lecturer' => 3, 'name' => 'Practical FPGA Design and Interfacing', 'description' => 'Hardware description, FPGA implementation, timing, and peripheral interfacing.'],
            ['id' => 'ECP2036', 'lecturer' => 1, 'name' => 'Manufacturing and Operations Management', 'description' => 'Production planning, quality, lean operations, and responsible engineering management.'],
            ['id' => 'ECP2046', 'lecturer' => 2, 'name' => 'Database Systems', 'description' => 'Relational design, SQL, indexing, transactions, and operational data quality.'],
            ['id' => 'EEN2026', 'lecturer' => 3, 'name' => 'Engineering Mathematics II', 'description' => 'Differential equations, transforms, matrices, and numerical methods.'],
            ['id' => 'ENG1026', 'lecturer' => 1, 'name' => 'Technical Communication', 'description' => 'Engineering reports, technical briefings, documentation, and presentation craft.'],
            ['id' => 'ERT3046', 'lecturer' => 2, 'name' => 'Control Systems', 'description' => 'Feedback control, transfer functions, stability, and controller tuning.'],
            ['id' => 'ECP4096', 'lecturer' => 3, 'name' => 'Cloud Applications', 'description' => 'Service design, deployment pipelines, observability, and cloud reliability.'],
        ])->mapWithKeys(function (array $subject) use ($lecturers) {
            $lecturer = $lecturers->get("lecturer{$subject['lecturer']}@gmail.com");

            return [
                $subject['id'] => Subject::updateOrCreate(
                    ['id' => $subject['id']],
                    [
                        'name' => $subject['name'],
                        'description' => $subject['description'],
                        'created_by' => $lecturer->id,
                    ],
                ),
            ];
        });
    }

    /**
     * @param  Collection<string, Subject>  $subjects
     * @return Collection<string, SchoolClass>
     */
    private function seedClasses(User $admin, Collection $subjects): Collection
    {
        return collect([
            [
                'id' => 'ROB-AUTO-2017',
                'name' => 'Robotics & Automation Cohort 2017',
                'subjects' => ['ECP1016', 'ECT1016', 'EEN1016', 'ERT3026', 'ERT3016', 'ERT3036'],
            ],
            [
                'id' => 'AI-APPS-2020',
                'name' => 'AI Applications Studio 2020',
                'subjects' => ['ECP3266', 'ECP3086', 'ECP1026', 'ECP2046', 'EEN1016'],
            ],
            [
                'id' => 'ENG-FOUNDATION',
                'name' => 'Engineering Foundation Lab',
                'subjects' => ['ECT1016', 'EEN1016', 'ERT2016', 'ECT3016', 'ENG1026'],
            ],
            [
                'id' => 'DATA-SYSTEMS-2024',
                'name' => 'Data Systems Cohort 2024',
                'subjects' => ['ECP2046', 'ECP1026', 'ECP3266', 'EEN2026', 'ECP2036'],
            ],
            [
                'id' => 'EMBEDDED-CLOUD-2025',
                'name' => 'Embedded Cloud Studio 2025',
                'subjects' => ['ECP4256', 'ECP4096', 'ERT3046', 'ECT1016', 'ENG1026'],
            ],
        ])->mapWithKeys(function (array $class) use ($admin, $subjects) {
            $schoolClass = SchoolClass::updateOrCreate(
                ['id' => $class['id']],
                [
                    'name' => $class['name'],
                    'created_by' => $admin->id,
                ],
            );

            $schoolClass->subjects()->sync(
                collect($class['subjects'])
                    ->filter(fn (string $subjectId) => $subjects->has($subjectId))
                    ->all(),
            );

            return [$class['id'] => $schoolClass];
        });
    }

    /**
     * @param  Collection<string, SchoolClass>  $classes
     * @param  Collection<string, User>  $students
     */
    private function enrollStudents(Collection $classes, Collection $students): void
    {
        $students->each(function (User $student) use ($classes) {
            $classId = $student->getAttribute('seed_class_id');
            $class = $classes->get($classId);

            if (! $class) {
                return;
            }

            $class->students()->syncWithoutDetaching([
                $student->id => ['assigned_at' => now()->subMonths(3), 'unassigned_at' => null],
            ]);
        });
    }

    /**
     * @return Collection<string, Exam>
     */
    private function seedExams(Collection $subjects): Collection
    {
        return collect($this->examBlueprints(now()))
            ->mapWithKeys(function (array $blueprint, string $key) use ($subjects) {
                $subject = $subjects->get($blueprint['subject_id']);

                $exam = Exam::updateOrCreate(
                    ['title' => $blueprint['title']],
                    [
                        'subject_id' => $blueprint['subject_id'],
                        'created_by' => $subject->created_by,
                        'time_limit_minutes' => $blueprint['time_limit_minutes'],
                        'default_question_weight' => $blueprint['default_question_weight'],
                        'status' => $blueprint['status'],
                        'starts_at' => $blueprint['starts_at'],
                        'ends_at' => $blueprint['ends_at'],
                    ],
                );

                $this->seedQuestions($exam, $blueprint['questions']);

                return [$key => $exam->fresh(['questions.options'])];
            });
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function examBlueprints(Carbon $now): array
    {
        return [
            'programming-lab' => [
                'title' => 'Computer and Program Design Lab Practical',
                'subject_id' => 'ECP1016',
                'time_limit_minutes' => 45,
                'default_question_weight' => 2.0,
                'status' => 'active',
                'starts_at' => $now->copy()->subMinutes(20),
                'ends_at' => $now->copy()->addHours(4),
                'questions' => [
                    $this->mcq('Which construct is best for reusing the same calculation with different inputs?', 2, [
                        ['A function with parameters', true],
                        ['A copied block of statements', false],
                        ['A global variable for every result', false],
                        ['A comment above the calculation', false],
                    ]),
                    $this->mcq('A loop should stop when the robot has processed all sensor samples. Which condition is safest?', 2, [
                        ['index < samples.length', true],
                        ['index <= samples.length', false],
                        ['index = samples.length', false],
                        ['samples.length < index', false],
                    ]),
                    $this->openText('Write pseudocode that reads five ultrasonic sensor distances, ignores negative readings, and returns the smallest valid distance.', 6),
                ],
            ],
            'automation-checkpoint' => [
                'title' => 'Automation Systems Checkpoint',
                'subject_id' => 'ERT3026',
                'time_limit_minutes' => 35,
                'default_question_weight' => 2.0,
                'status' => 'active',
                'starts_at' => $now->copy()->subHour(),
                'ends_at' => $now->copy()->addHours(5),
                'questions' => [
                    $this->mcq('Which device converts a physical condition into an electrical signal for a controller?', 2, [
                        ['Sensor', true],
                        ['Actuator', false],
                        ['Relay output', false],
                        ['Human-machine interface', false],
                    ]),
                    $this->mcq('In a conveyor reject station, what is the actuator most likely responsible for?', 2, [
                        ['Pushing a failed item off the line', true],
                        ['Measuring ambient temperature', false],
                        ['Storing historical grades', false],
                        ['Normalizing a database table', false],
                    ]),
                    $this->openText('Explain one interlock you would add to keep an automated conveyor cell safe during maintenance.', 5),
                ],
            ],
            'circuit-midterm' => [
                'title' => 'Circuit Theory Midterm',
                'subject_id' => 'ECT1016',
                'time_limit_minutes' => 60,
                'default_question_weight' => 2.0,
                'status' => 'closed',
                'starts_at' => $now->copy()->subDays(10),
                'ends_at' => $now->copy()->subDays(10)->addHour(),
                'questions' => [
                    $this->mcq('Two 10 ohm resistors are connected in series. What is the equivalent resistance?', 2, [
                        ['20 ohm', true],
                        ['10 ohm', false],
                        ['5 ohm', false],
                        ['0 ohm', false],
                    ]),
                    $this->mcq('Kirchhoff\'s current law is based on which conservation principle?', 2, [
                        ['Charge conservation', true],
                        ['Momentum conservation', false],
                        ['Mass conservation', false],
                        ['Thermal equilibrium', false],
                    ]),
                    $this->openText('A measured node voltage is lower than expected. Describe two circuit checks you would perform before replacing components.', 6),
                ],
            ],
            'math-worked-solutions' => [
                'title' => 'Engineering Mathematics Worked Solutions',
                'subject_id' => 'EEN1016',
                'time_limit_minutes' => 75,
                'default_question_weight' => 3.0,
                'status' => 'closed',
                'starts_at' => $now->copy()->subDays(3),
                'ends_at' => $now->copy()->subDays(3)->addMinutes(75),
                'questions' => [
                    $this->mcq('If f(x) = x^2, what is f\'(x)?', 3, [
                        ['2x', true],
                        ['x', false],
                        ['x^3/3', false],
                        ['2', false],
                    ]),
                    $this->openText('Show the main steps for finding the stationary points of f(x) = x^3 - 6x^2 + 9x + 2.', 7),
                    $this->openText('Explain how you would use a determinant to decide whether two vectors in R2 are linearly independent.', 5),
                ],
            ],
            'ai-final' => [
                'title' => 'Artificial Intelligence and Applications Final',
                'subject_id' => 'ECP3266',
                'time_limit_minutes' => 90,
                'default_question_weight' => 2.5,
                'status' => 'draft',
                'starts_at' => $now->copy()->addDays(7),
                'ends_at' => $now->copy()->addDays(7)->addMinutes(90),
                'questions' => [
                    $this->mcq('Which search strategy expands the shallowest unexplored node first?', 2.5, [
                        ['Breadth-first search', true],
                        ['Depth-first search', false],
                        ['Hill climbing', false],
                        ['Random restart', false],
                    ]),
                    $this->mcq('A confusion matrix is most directly used to evaluate what kind of model?', 2.5, [
                        ['Classification model', true],
                        ['Sorting algorithm', false],
                        ['Database migration', false],
                        ['Power transformer', false],
                    ]),
                    $this->openText('Propose a small AI feature for an exam portal and name one risk you would monitor after release.', 8),
                ],
            ],
            'robotics-practical' => [
                'title' => 'Robotics Motion Planning Practical',
                'subject_id' => 'ERT3016',
                'time_limit_minutes' => 50,
                'default_question_weight' => 2.0,
                'status' => 'active',
                'starts_at' => $now->copy()->subMinutes(5),
                'ends_at' => $now->copy()->addHours(2),
                'questions' => [
                    $this->mcq('What does inverse kinematics calculate?', 2, [
                        ['Joint values needed to reach a desired end-effector pose', true],
                        ['Battery percentage after a mission', false],
                        ['The GPA of a robotics cohort', false],
                        ['The serial number of a motor driver', false],
                    ]),
                    $this->mcq('Which sensor is commonly used for wheel odometry feedback?', 2, [
                        ['Encoder', true],
                        ['Microphone', false],
                        ['Barometer', false],
                        ['LCD display', false],
                    ]),
                    $this->openText('Describe how you would test whether a mobile robot can recover after briefly losing line-tracking input.', 6),
                ],
            ],
            'fpga-lab' => [
                'title' => 'Practical FPGA Design Timing Lab',
                'subject_id' => 'ECP4256',
                'time_limit_minutes' => 40,
                'default_question_weight' => 2.0,
                'status' => 'closed',
                'starts_at' => $now->copy()->subDays(21),
                'ends_at' => $now->copy()->subDays(21)->addMinutes(40),
                'questions' => [
                    $this->mcq('What is the main purpose of a timing constraint in FPGA design?', 2, [
                        ['Tell the tool the clock and path timing requirements', true],
                        ['Choose a student password', false],
                        ['Rename every signal automatically', false],
                        ['Disable synthesis warnings', false],
                    ]),
                    $this->openText('Explain one debugging step when a design works in simulation but fails on the FPGA board.', 6),
                ],
            ],
            'database-indexing' => [
                'title' => 'Database Systems Indexing Quiz',
                'subject_id' => 'ECP2046',
                'time_limit_minutes' => 30,
                'default_question_weight' => 2.0,
                'status' => 'active',
                'starts_at' => $now->copy()->subMinutes(10),
                'ends_at' => $now->copy()->addHours(3),
                'questions' => [
                    $this->mcq('Which index is usually most useful for equality lookups on a single unique column?', 2, [
                        ['B-tree index', true],
                        ['Full table scan', false],
                        ['Random sleep', false],
                        ['Manual spreadsheet sort', false],
                    ]),
                    $this->openText('Describe one trade-off of adding many indexes to a write-heavy table.', 5),
                ],
            ],
            'math-ii-methods' => [
                'title' => 'Engineering Mathematics II Methods Check',
                'subject_id' => 'EEN2026',
                'time_limit_minutes' => 55,
                'default_question_weight' => 2.5,
                'status' => 'draft',
                'starts_at' => $now->copy()->addDays(5),
                'ends_at' => $now->copy()->addDays(5)->addMinutes(55),
                'questions' => [
                    $this->mcq('Laplace transforms are commonly used to solve which kind of problem?', 2.5, [
                        ['Differential equations', true],
                        ['Color palette selection', false],
                        ['Email verification', false],
                        ['String slug formatting', false],
                    ]),
                    $this->openText('Explain how eigenvalues can help describe the behavior of a linear system.', 6),
                ],
            ],
            'controls-stability' => [
                'title' => 'Control Systems Stability Drill',
                'subject_id' => 'ERT3046',
                'time_limit_minutes' => 40,
                'default_question_weight' => 2.0,
                'status' => 'active',
                'starts_at' => $now->copy()->subHour(),
                'ends_at' => $now->copy()->addHours(6),
                'questions' => [
                    $this->mcq('What does negative feedback usually improve in a control loop?', 2, [
                        ['Stability and disturbance rejection', true],
                        ['Password length', false],
                        ['Table column count', false],
                        ['File upload speed only', false],
                    ]),
                    $this->openText('Name one symptom of an over-tuned controller in a physical system.', 5),
                ],
            ],
            'cloud-observability' => [
                'title' => 'Cloud Applications Observability Lab',
                'subject_id' => 'ECP4096',
                'time_limit_minutes' => 45,
                'default_question_weight' => 2.0,
                'status' => 'closed',
                'starts_at' => $now->copy()->subDays(14),
                'ends_at' => $now->copy()->subDays(14)->addMinutes(45),
                'questions' => [
                    $this->mcq('Which signal most directly tells you how often requests fail?', 2, [
                        ['Error rate', true],
                        ['Logo size', false],
                        ['Number of menu items', false],
                        ['Keyboard layout', false],
                    ]),
                    $this->openText('List two metrics you would monitor for an exam-taking page during peak usage.', 6),
                ],
            ],
        ];
    }

    /**
     * @param  array<int, array{0: string, 1: bool}>  $options
     * @return array<string, mixed>
     */
    private function mcq(string $text, float $weight, array $options): array
    {
        return compact('text', 'weight', 'options') + ['type' => 'mcq'];
    }

    /**
     * @return array<string, mixed>
     */
    private function openText(string $text, float $weight): array
    {
        return compact('text', 'weight') + ['type' => 'open_text'];
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function seedQuestions(Exam $exam, array $questions): void
    {
        foreach ($questions as $index => $questionBlueprint) {
            $question = Question::updateOrCreate(
                ['exam_id' => $exam->id, 'order' => $index + 1],
                [
                    'type' => $questionBlueprint['type'],
                    'text' => $questionBlueprint['text'],
                    'weight' => $questionBlueprint['weight'],
                ],
            );

            if ($question->isMcq()) {
                foreach ($questionBlueprint['options'] as $optionIndex => [$body, $isCorrect]) {
                    Option::updateOrCreate(
                        ['question_id' => $question->id, 'order' => $optionIndex + 1],
                        ['body' => $body, 'is_correct' => $isCorrect],
                    );
                }

                $question->options()->where('order', '>', count($questionBlueprint['options']))->delete();
            } else {
                $question->options()->delete();
            }
        }

        $exam->questions()->where('order', '>', count($questions))->delete();
    }

    /**
     * @param  Collection<string, Exam>  $exams
     * @param  Collection<string, User>  $students
     */
    private function seedAttempts(Collection $exams, Collection $students): void
    {
        $this->seedAttempt(
            exam: $exams['programming-lab'],
            student: $students['student1@gmail.com'],
            state: 'pending',
            startedAt: now()->subMinutes(12),
            submittedAt: null,
            answers: [
                1 => ['option_order' => 1],
                2 => ['option_order' => 2],
                3 => ['text' => 'Loop through the five readings, skip values below zero, and keep a minDistance variable that is replaced whenever a smaller valid distance appears.'],
            ],
        );

        $this->seedAttempt(
            exam: $exams['circuit-midterm'],
            student: $students['student1@gmail.com'],
            state: 'scored',
            startedAt: now()->subDays(10)->addMinutes(3),
            submittedAt: now()->subDays(10)->addMinutes(54),
            answers: [
                1 => ['option_order' => 1],
                2 => ['option_order' => 1],
                3 => [
                    'text' => 'I would verify the reference ground, measure the supply rail, inspect resistor values, and check for a loose jumper before replacing components.',
                    'score' => 5.0,
                    'reviewer_comment' => 'Good diagnostic order. Add expected meter ranges next time.',
                ],
            ],
        );

        $this->seedAttempt(
            exam: $exams['math-worked-solutions'],
            student: $students['student1@gmail.com'],
            state: 'pending_review',
            startedAt: now()->subDays(3)->addMinutes(4),
            submittedAt: now()->subDays(3)->addMinutes(68),
            answers: [
                1 => ['option_order' => 1],
                2 => ['text' => 'Differentiate, set 3x^2 - 12x + 9 equal to zero, divide by 3, then solve x^2 - 4x + 3 = 0 for x = 1 and x = 3.'],
                3 => ['text' => 'Put the two vectors into a 2 by 2 matrix. If the determinant is not zero, neither vector is a scalar multiple of the other.'],
            ],
        );

        $this->seedAttempt(
            exam: $exams['fpga-lab'],
            student: $students['student3@gmail.com'],
            state: 'scored',
            startedAt: now()->subDays(21)->addMinutes(2),
            submittedAt: now()->subDays(21)->addMinutes(39),
            answers: [
                1 => ['option_order' => 1],
                2 => [
                    'text' => 'I would check pin assignments, clock constraints, reset polarity, and whether the board input is debounced.',
                    'score' => 5.5,
                    'reviewer_comment' => 'Strong practical checklist.',
                ],
            ],
        );

        $this->seedAttempt(
            exam: $exams['robotics-practical'],
            student: $students['student2@gmail.com'],
            state: 'pending',
            startedAt: now()->subMinutes(3),
            submittedAt: null,
            answers: [
                1 => ['option_order' => 1],
            ],
        );

        $this->seedAttempt(
            exam: $exams['automation-checkpoint'],
            student: $students['student5@gmail.com'],
            state: 'pending_review',
            startedAt: now()->subMinutes(44),
            submittedAt: now()->subMinutes(8),
            answers: [
                1 => ['option_order' => 1],
                2 => ['option_order' => 1],
                3 => ['text' => 'Use a keyed maintenance switch that disables actuator outputs and requires a supervisor reset before the conveyor can restart.'],
            ],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $answers
     */
    private function seedAttempt(
        Exam $exam,
        User $student,
        string $state,
        Carbon $startedAt,
        ?Carbon $submittedAt,
        array $answers,
    ): void {
        $session = ExamSession::updateOrCreate(
            ['exam_id' => $exam->id, 'user_id' => $student->id],
            [
                'state' => $state,
                'started_at' => $startedAt,
                'submitted_at' => $submittedAt,
                'score_raw' => null,
                'score_max' => null,
            ],
        );

        $exam->questions->each(function (Question $question) use ($session, $answers) {
            if (! isset($answers[$question->order])) {
                return;
            }

            $answer = $answers[$question->order];

            if ($question->isMcq()) {
                $option = $question->options->firstWhere('order', $answer['option_order']);

                Answer::updateOrCreate(
                    ['exam_session_id' => $session->id, 'question_id' => $question->id],
                    [
                        'type' => 'mcq',
                        'selected_option_id' => $option?->id,
                        'text_answer' => null,
                        'score' => $option?->is_correct ? $question->effectiveWeight() : 0.0,
                        'reviewer_comment' => null,
                    ],
                );

                return;
            }

            Answer::updateOrCreate(
                ['exam_session_id' => $session->id, 'question_id' => $question->id],
                [
                    'type' => 'open_text',
                    'selected_option_id' => null,
                    'text_answer' => $answer['text'],
                    'score' => $answer['score'] ?? null,
                    'reviewer_comment' => $answer['reviewer_comment'] ?? null,
                ],
            );
        });

        $session->answers()
            ->whereNotIn('question_id', $exam->questions->pluck('id'))
            ->delete();

        if ($state === 'scored') {
            $session->computeScores();
        }
    }
}
