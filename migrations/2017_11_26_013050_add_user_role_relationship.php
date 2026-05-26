<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUserRoleRelationship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ensure there is at least one role before adding the foreign key constraint.
        // Seeders run after migrations, so the roles table may be empty at this point.
        $defaultRoleId = DB::table('roles')->value('id');

        if (! $defaultRoleId) {
            $defaultRoleId = DB::table('roles')->insertGetId([
                'name'         => 'admin',
                'display_name' => 'Administrator',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // Set any NULL role_id to the default role to prevent data truncation
        // errors when adding the NOT NULL constraint.
        if (Schema::hasTable('users')) {
            DB::table('users')
                ->whereNull('role_id')
                ->update(['role_id' => $defaultRoleId]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('role_id')->nullable()->change();
        });
    }
}
