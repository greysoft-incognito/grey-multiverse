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
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_id')->nullable()->constrained('portals', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('title')->nullable();
            $table->string('external_link')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('banner_title')->nullable();
            $table->text('banner_info')->fullText()->nullable();
            $table->json('socials')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->boolean('dont_notify')->default(false);
            $table->boolean('require_auth')->default(false);
            $table->text('data_emails')->nullable();
            $table->string('template')->default('default');
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
        Schema::dropIfExists('forms');
    }
};
