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
            if (! Schema::hasColumn('form_data', 'draft')) {
                $table->json('draft')->nullable()->after('data');
            }
            if (! Schema::hasColumn('form_data', 'rank')) {
                $table->integer('rank')->nullable()->default(0)->after('draft');
            }
            if (! Schema::hasColumn('form_data', 'phone_country')) {
                $table->string('phone_country')->nullable()->after('rank');
            }
        });

        Schema::table('form_field_groups', function (Blueprint $table) {
            if (! Schema::hasColumn('form_field_groups', 'requires_auth')) {
                $table->boolean('requires_auth')->default(false)->after('authenticator');
            }
        });

        Schema::table('form_fields', function (Blueprint $table) {
            if (! Schema::hasColumn('form_fields', 'points')) {
                $table->integer('points')->nullable()->default(0)->after('max');
            }
        });

        Schema::table('form_fields', function (Blueprint $table) {
            if (! Schema::hasColumn('form_fields', 'accept')) {
                $table->string('accept')->nullable()->after('hint');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_data', function (Blueprint $table) {
            if (Schema::hasColumn('form_data', 'rank')) {
                $table->dropColumn(['rank']);
            }
            if (Schema::hasColumn('form_data', 'draft')) {
                $table->dropColumn(['draft']);
            }
            if (Schema::hasColumn('form_data', 'phone_country')) {
                $table->dropColumn(['phone_country']);
            }
        });

        Schema::table('form_field_groups', function (Blueprint $table) {
            if (Schema::hasColumn('form_field_groups', 'requires_auth')) {
                $table->dropColumn(['requires_auth']);
            }
        });

        Schema::table('form_fields', function (Blueprint $table) {
            if (Schema::hasColumn('form_fields', 'points')) {
                $table->dropColumn(['points']);
            }
        });

        Schema::table('form_fields', function (Blueprint $table) {
            if (Schema::hasColumn('form_fields', 'accept')) {
                $table->dropColumn(['accept']);
            }
        });
    }
};
