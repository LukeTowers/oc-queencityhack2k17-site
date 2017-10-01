<?php

namespace Look\Conversation\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateParticipantsTable extends Migration
{

    public function up()
    {
        Schema::create('look_conversation_participants', function ($table) {
            $table->increments('id');
            $table->integer('message_id')->unsigned()->index()->nullable();
            $table->integer('user_id')->unsigned()->index();
            $table->string('user_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('look_conversation_participants');
    }

}