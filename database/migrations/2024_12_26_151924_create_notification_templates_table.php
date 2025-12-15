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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->string('label')->nullable();
            $table->string('description')->nullable();
            $table->string('subject')->nullable();
            $table->text('plain')->nullable();
            $table->text('html')->nullable();
            $table->text('sms')->nullable();
            $table->text('footnote')->nullable();
            $table->text('copyright')->nullable();
            $table->json('args')->nullable();
            $table->json('lines')->nullable();
            $table->json('allowed')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
