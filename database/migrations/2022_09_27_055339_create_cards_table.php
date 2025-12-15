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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->nullable()->constrained('sections', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('title');
            $table->string('content')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('badge_text')->nullable();
            $table->string('sticker_text')->nullable();
            $table->string('component')->nullable();
            $table->integer('rating')->nullable();
            $table->double('price')->default(0.00);
            $table->json('infos')->nullable();
            $table->json('link')->nullable();
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('cards');
    }
};
