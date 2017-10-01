<?php

namespace Look\Conversation\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateMessagesTable extends Migration
{

    public function up()
    {
        Schema::create('look_conversation_messages', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('thread_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
            $table->string('user_type');
            $table->mediumText('body');
            $table->timestamps();
        });
        
        Schema::table('look_conversation_messages', function ($table) {
	        $table->index(['user_id', 'user_type'], 'message_owner_user_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('look_conversation_messages');
    }

}