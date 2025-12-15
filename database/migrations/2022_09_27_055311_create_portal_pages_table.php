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
        Schema::create('portal_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_id')->constrained('portals')->cascadeOnDelete();
            $table->string('slug')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('keywords')->nullable();
            $table->text('content')->fulltext()->nullable();
            $table->text('meta')->nullable();
            $table->string('component')->nullable();
            $table->string('footer_group')->nullable();
            $table->string('image')->nullable();
            $table->boolean('index')->default(false);
            $table->boolean('in_navbar')->default(false);
            $table->boolean('in_footer')->default(false);
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
        Schema::dropIfExists('portal_pages');
    }
};
