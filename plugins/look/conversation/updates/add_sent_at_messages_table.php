<?php

namespace Look\Conversation\Updates;

use DB;
use Schema;
use October\Rain\Database\Updates\Migration;

use Look\Conversation\Models\Status as StatusModel;
use Look\Conversation\Models\Message as MessageModel;

class AddSentAtMessagesTable extends Migration
{

    public function up()
    {
        Schema::table('look_conversation_messages', function ($table) {
	        $table->timestamp('sent_at')->nullable();
        });
        
        // Update all previously sent messages to have the correct dates
        MessageModel::whereNull('sent_at')
        	->whereHas('status', function($q) {
	        	$q->where('status', '!=', StatusModel::DRAFT);
        	})
        	->update([
	        	'sent_at' => DB::raw("`updated_at`"),
        	]);
    }

    public function down()
    {
        Schema::table('look_conversation_messages', function ($table) {
	        $table->dropColumn('sent_at');
        });
    }

}