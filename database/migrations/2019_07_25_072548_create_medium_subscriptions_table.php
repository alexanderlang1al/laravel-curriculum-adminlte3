<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediumSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medium_subscriptions', function (Blueprint $table) {
            $table->unsignedbigInteger('medium_id');
            $table->string('subscribable_type');
            $table->unsignedbigInteger('subscribable_id');

            $table->primary(['medium_id', 'subscribable_type', 'subscribable_id'], 'm_subscr_m_id_subscr_type_subscr_id_primary');

            $table->unsignedbigInteger('sharing_level_id');
            $table->boolean('visibility');
            $table->unsignedbigInteger('owner_id');
            $table->timestamps();

            $table->foreign('medium_id')->references('id')->on('media');
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
        Schema::dropIfExists('medium_subscriptions');
    }
}
