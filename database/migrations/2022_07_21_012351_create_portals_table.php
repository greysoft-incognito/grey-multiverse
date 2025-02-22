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
        Schema::create('portals', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable();
            $table->string('name')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('banner')->nullable();
            $table->string('description')->nullable();
            $table->string('footer_info')->nullable();
            $table->text('meta')->nullable();
            $table->boolean('allow_registration')->default(false);
            $table->string('registration_model')->nullable();
            $table->string('reg_link_title')->nullable();
            $table->string('reg_form_id')->nullable();
            $table->double('reg_fee')->default(0.00);
            $table->string('login_link_title')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->json('socials')->nullable();
            $table->json('footer_groups')->nullable();
            $table->string('copyright')->nullable();
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
        Schema::dropIfExists('portals');
    }
};
