<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepositorySubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repository_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('subscribable_type');
            $table->unsignedbigInteger('subscribable_id');
            $table->string('repository'); //eg 'edu-sharing'
            $table->string('value', 500); //eg 'edu-sharing'
            $table->unsignedbigInteger('sharing_level_id');
            $table->boolean('visibility');
            $table->unsignedbigInteger('owner_id');
            $table->timestamps();

            $table->foreign('sharing_level_id')->references('id')->on('sharing_levels');
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
        Schema::dropIfExists('repository_subscriptions');
    }
}
