<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained('courses', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->string('preview')->nullable();
            $table->string('file')->nullable();
            $table->text('transcript')->nullable()->fulltext();
            $table->string('icon')->nullable();
            $table->string('component')->nullable();
            $table->string('duration')->nullable();
            $table->integer('dificulty')->default(1);
            $table->integer('order')->default(1);
            $table->json('availability')->comment(json_encode(['virtual', 'physical', 'recorded', 'live']))->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_modules');
    }
};
