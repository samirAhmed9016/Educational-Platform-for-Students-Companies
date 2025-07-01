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
        Schema::create('progress', function (Blueprint $table) {

            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade'); // added course_id
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');

            $table->unsignedTinyInteger('progress_percentage')->default(0); // 0-100%
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();

            $table->text('notes')->nullable(); // optional notes or feedback

            $table->timestamps();

            $table->unique(['user_id', 'lesson_id']);
            $table->index(['user_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress');
    }
};
