<?php namespace TeamAte\Ccrezqs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTeamateCcrezqsFosters extends Migration
{
    public function up()
    {
        Schema::create('teamate_ccrezqs_fosters', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('surname');
            $table->string('email');
            $table->string('status');
            $table->text('contact_info');
            $table->text('residence');
            $table->text('personal_info');
            $table->text('animal_info');
            $table->text('foster_info');
            $table->text('other_info');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('teamate_ccrezqs_fosters');
    }
}
