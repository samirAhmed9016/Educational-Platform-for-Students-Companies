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
        Schema::create('lessons', function (Blueprint $table) {
            $table->unsignedBigInteger('id');          // no auto-increment here
            $table->unsignedBigInteger('course_id');
            $table->string('title');
            $table->text('content');
            $table->string('video_url')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->primary(['id', 'course_id']);      // composite primary key
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
