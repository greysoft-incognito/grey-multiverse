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
        Schema::create('form_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('priority')->default(1);
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('content')->fullText()->nullable();
            $table->json('list')->nullable();
            $table->string('icon')->nullable();
            $table->string('icon_color')->nullable();
            $table->boolean('increment_icon')->default(false);
            $table->string('image')->nullable();
            $table->string('position')->default('left');
            $table->enum('type', ['text', 'list', 'cta', 'video'])->default('text');
            $table->string('template')->nullable();
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
        Schema::dropIfExists('form_infos');
    }
};
