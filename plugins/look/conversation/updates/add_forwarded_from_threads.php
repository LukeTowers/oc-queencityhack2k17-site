<?php

namespace Look\Conversation\Updates;

use DB;
use Schema;
use October\Rain\Database\Updates\Migration;

use Look\Conversation\Models\Status as StatusModel;
use Look\Conversation\Models\Message as MessageModel;

class AddForwardedFromThreads extends Migration
{

    public function up()
    {
        Schema::table('look_conversation_threads', function ($table) {
	        $table->integer('forwarded_from')->unsigned()->nullable();
        });
    }

    public function down()
    {
        Schema::table('look_conversation_threads', function ($table) {
	        $table->dropColumn('forwarded_from');
        });
    }

}