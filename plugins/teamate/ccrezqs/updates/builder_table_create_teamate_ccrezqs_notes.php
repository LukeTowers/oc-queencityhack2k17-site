<?php namespace TeamAte\Ccrezqs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTeamateCcrezqsNotes extends Migration
{
    public function up()
    {
        Schema::create('teamate_ccrezqs_notes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('target_id')->unsigned();
            $table->string('target_type');
            $table->string('title');
            $table->text('contents');
            $table->timestamp('created_at');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('teamate_ccrezqs_notes');
    }
}
