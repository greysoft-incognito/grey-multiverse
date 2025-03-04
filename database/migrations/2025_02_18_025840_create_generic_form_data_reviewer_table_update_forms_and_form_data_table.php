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
        Schema::create('form_data_reviewer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('form_data_id')->constrained('form_data')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::table('form_data', function (Blueprint $table) {
            if (!Schema::hasColumn('form_data', 'status')) {
                $table->enum('status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending')->after('data');
            }
        });

        Schema::table('forms', function (Blueprint $table) {
            if (!Schema::hasColumn('forms', 'config')) {
                $table->json('config')->nullable()->after('template');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_data_reviewer');
        Schema::table('form_data', function (Blueprint $table) {
            if (Schema::hasColumn('form_data', 'status')) {
                $table->dropColumn('status');
            }
        });
        Schema::table('forms', function (Blueprint $table) {
            if (Schema::hasColumn('forms', 'config')) {
                $table->dropColumn('config');
            }
        });
    }
};
