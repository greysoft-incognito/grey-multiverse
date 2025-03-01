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
        Schema::table('form_data', function (Blueprint $table) {
            $table->integer('rank')->nullable()->default(0)->after('status');
        });

        Schema::table('form_field_groups', function (Blueprint $table) {
            $table->boolean('requires_auth')->default(false)->after('authenticator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_data', function (Blueprint $table) {
            $table->dropColumn(['rank']);
        });

        Schema::table('form_field_groups', function (Blueprint $table) {
            $table->dropColumn(['requires_auth']);
        });
    }
};
