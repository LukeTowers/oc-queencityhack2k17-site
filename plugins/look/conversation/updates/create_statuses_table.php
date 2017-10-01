<?php

namespace Look\Conversation\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateStatusesTable extends Migration
{

    public function up()
    {
        Schema::create('look_conversation_statuses', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('message_id')->unsigned()->index()->nullable();
            $table->integer('user_id')->unsigned();
            $table->string('user_type');
            $table->string('status');
            $table->timestamps();
        });
        
        Schema::table('look_conversation_statuses', function ($table) {
	        $table->index(['user_id', 'user_type'], 'status_owner_user_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('look_conversation_statuses');
    }

}