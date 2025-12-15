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
        Schema::table('forms', function (Blueprint $table) {
            $table->text('success_message')->nullable()->after('socials');
            $table->text('approval_message')->nullable()->after('success_message');
            $table->text('rejection_message')->nullable()->after('approval_message');
            $table->text('failure_message')->nullable()->after('rejection_message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('success_message');
            $table->dropColumn('failure_message');
            $table->dropColumn('rejection_message');
            $table->dropColumn('failure_message');
        });
    }
};
