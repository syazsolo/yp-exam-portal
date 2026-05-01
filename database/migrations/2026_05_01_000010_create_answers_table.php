<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained('exam_sessions')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('type'); // mcq | open_text (STI discriminator)
            $table->foreignId('selected_option_id')->nullable()->constrained('options')->nullOnDelete();
            $table->text('text_answer')->nullable();
            $table->float('score')->nullable();
            $table->text('reviewer_comment')->nullable();
            $table->timestamps();

            $table->unique(['exam_session_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
