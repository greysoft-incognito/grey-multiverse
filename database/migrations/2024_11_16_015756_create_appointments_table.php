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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requestor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('invitee_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->enum('time_slot', ['morning', 'afternoon', 'evening']);
            $table->integer('duration')->default(15); // Can be 15, 20, 25 or 30
            $table->enum('status', ['pending', 'confirmed', 'rescheduled', 'canceled'])->default('pending');
            $table->integer('table_number')->nullable();
            $table->timestamp('booked_for')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
