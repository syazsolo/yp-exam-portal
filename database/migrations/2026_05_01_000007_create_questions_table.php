<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->string('type'); // mcq | open_text
            $table->text('text');
            $table->unsignedSmallInteger('order')->default(0);
            $table->float('weight')->nullable();
            $table->timestamps();
        });

        // Enforce weight > 0 at DB level (NULL allowed → falls back to exam default).
        // SQLite + MySQL 8+ + Postgres all support CHECK constraints.
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            // SQLite: trigger on insert/update — CHECK constraints in CREATE TABLE
            // can't be added after-the-fact via Schema, so use a trigger instead.
            DB::statement(<<<'SQL'
                CREATE TRIGGER questions_weight_positive_insert
                BEFORE INSERT ON questions
                FOR EACH ROW
                WHEN NEW.weight IS NOT NULL AND NEW.weight <= 0
                BEGIN
                    SELECT RAISE(ABORT, 'questions.weight must be > 0');
                END
            SQL);
            DB::statement(<<<'SQL'
                CREATE TRIGGER questions_weight_positive_update
                BEFORE UPDATE ON questions
                FOR EACH ROW
                WHEN NEW.weight IS NOT NULL AND NEW.weight <= 0
                BEGIN
                    SELECT RAISE(ABORT, 'questions.weight must be > 0');
                END
            SQL);
        } else {
            DB::statement('ALTER TABLE questions ADD CONSTRAINT questions_weight_positive CHECK (weight IS NULL OR weight > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
