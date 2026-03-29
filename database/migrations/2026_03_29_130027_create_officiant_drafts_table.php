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
        Schema::create('officiant_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('couple_id')->constrained();
            $table->string('status')->default('in_progress');
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->longText('generated_text')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'couple_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('officiant_drafts');
    }
};
