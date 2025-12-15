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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_page_id')->nullable()->constrained('portal_pages', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('title')->nullable();
            $table->string('title_highlight')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('minititle')->nullable();
            $table->text('content')->fulltext()->nullable();
            $table->string('type')->default('banner')->comment('banner, content, form, video', 'cards', 'cta');
            $table->string('image')->nullable();
            $table->string('image_position')->default('right')->comment('left, right, top, bottom');
            $table->string('image2')->nullable();
            $table->string('background')->nullable();
            $table->json('link')->nullable();
            $table->string('component')->nullable();
            $table->string('video_link')->nullable();
            $table->json('list')->nullable();
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
        Schema::dropIfExists('sections');
    }
};
