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
        Schema::table('form_data', function (Blueprint $table) {
            if (! Schema::hasColumn('form_data', 'status_reason')) {
                $table->text('status_reason')->nullable()->after('status');
            }

            if (! Schema::hasColumn('form_data', 'reviewer_id')) {
                $table->foreignId('reviewer_id')->nullable()->after('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_data', function (Blueprint $table) {
            if (Schema::hasColumn('form_data', 'status_reason')) {
                $table->dropColumn('status_reason');
            }

            if (Schema::hasColumn('form_data', 'reviewer_id')) {
                $table->dropConstrainedForeignId('reviewer_id');
            }
        });
    }
};
