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
        Schema::create('vows_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vows_draft_id')->constrained()->cascadeOnDelete();
            $table->string('question_key');
            $table->longText('answer_text');
            $table->unsignedTinyInteger('step_order');
            $table->timestamps();
            $table->unique(['vows_draft_id', 'question_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vows_answers');
    }
};
