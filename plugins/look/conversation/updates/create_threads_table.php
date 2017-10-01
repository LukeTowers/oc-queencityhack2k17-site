<?php

namespace Look\Conversation\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateThreadsTable extends Migration
{

    public function up()
    {
        Schema::create('look_conversation_threads', function ($table) {
            $table->increments('id');
            $table->text('subject');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('look_conversation_threads');
    }

}