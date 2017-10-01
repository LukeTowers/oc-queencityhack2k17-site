<?php namespace Look\Conversation\Updates;

use DB;
use Schema;
use October\Rain\Database\Updates\Migration;

use Look\Conversation\Models\Status as StatusModel;
use Look\Conversation\Models\Message as MessageModel;

class AddSoftDeletesMessages extends Migration
{

    public function up()
    {
        Schema::table('look_conversation_messages', function ($table) {
	        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('look_conversation_messages', function ($table) {
	        $table->dropSoftDeletes();
        });
    }

}