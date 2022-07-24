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
        Schema::create('generic_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('label')->nullable()->default('Field');
            $table->string('name')->nullable()->default('field');
            $table->string('value')->nullable();
            $table->string('field_id')->nullable()->default('field');
            $table->string('hint')->nullable();
            $table->string('custom_error')->nullable();
            $table->json('options')->nullable();
            $table->string('required_if')->nullable();
            $table->boolean('restricted')->default(false);
            $table->boolean('required')->default(true);
            $table->boolean('key')->default(false);
            $table->enum('element', ['input', 'textarea', 'select'])->default('input');
            $table->enum('type', [
                'hidden',
                'text',
                'number',
                'email',
                'password',
                'date',
                'time',
                'datetime-local',
                'file',
                'tel',
                'url',
                'checkbox',
                'radio',
            ])->default('text');
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
        Schema::dropIfExists('generic_form_fields');
    }
};