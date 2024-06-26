<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeriodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->timestamp('begin');
            $table->timestamp('end')->nullable();

            //$table->unsignedbigInteger('organization_id')->nullable();

            $table->unsignedbigInteger('owner_id')->nullable();

            $table->timestamps();

            // $table->foreign('organization_id')->references('id')->on('organizations'); //will be dropped
            $table->foreign('owner_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('periods');
    }
}
