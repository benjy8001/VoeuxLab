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
        Schema::create('ceremonies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained();
            $table->json('program')->nullable();
            $table->text('notes_officiant')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->unique('couple_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ceremonies');
    }
};
