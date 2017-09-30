<?php namespace TeamAte\Ccrezqs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTeamateCcrezqsDogs extends Migration
{
    public function up()
    {
        Schema::create('teamate_ccrezqs_dogs', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255);
            $table->string('gender', 255);
            $table->date('birthdate');
            $table->string('breed', 255);
            $table->string('colouring', 255);
            $table->string('litter', 255)->nullable();
            $table->date('care_start');
            $table->text('medical_info')->nullable();
            $table->text('general_info')->nullable();
            $table->string('size', 255)->nullable();
            $table->string('fixed', 255)->nullable();
            $table->string('from', 255)->nullable();
            $table->integer('foster_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('teamate_ccrezqs_dogs');
    }
}
