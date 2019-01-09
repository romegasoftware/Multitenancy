<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('multitenancy.table_names');

        Schema::create($tableNames['tenants'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('domain')->unique();
            $table->timestamps();
        });

        Schema::create($tableNames['tenant_user'], function (Blueprint $table) use ($tableNames) {
            $table->increments('id');

            $table->unsignedInteger('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on($tableNames['tenants'])
                ->onDelete('cascade');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on($tableNames['users'])
                ->onDelete('cascade');

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
        $tableNames = config('multitenancy.table_names');
        Schema::dropIfExists($tableNames['tenants']);
        Schema::dropIfExists($tableNames['tenant_user']);
    }
}
