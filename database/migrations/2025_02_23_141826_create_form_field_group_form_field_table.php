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
        Schema::create('form_field_group_form_field', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_field_group_id')->constrained('form_field_groups')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('form_field_id')->constrained('form_fields')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_field_group_form_field');
    }
};
