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
        Schema::create('couples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spouse_1_id')->constrained('users');
            $table->foreignId('spouse_2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('officiant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invitation_code', 8)->unique();
            $table->date('ceremony_date')->nullable();
            $table->string('ceremony_location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('couples', function (Blueprint $table) {
            $table->dropForeign(['spouse_1_id']);
            $table->dropForeign(['spouse_2_id']);
            $table->dropForeign(['officiant_id']);
        });
        Schema::dropIfExists('couples');
    }
};
