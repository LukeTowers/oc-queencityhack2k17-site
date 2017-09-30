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
            $table->integer('foster_id')->nullable()->unsigned();
            $table->string('name');
            $table->string('status');
            $table->string('gender');
            $table->string('size');
            $table->boolean('is_fixed')->default(0);
            $table->string('from')->nullable();
            $table->date('birthdate')->nullable();
            $table->date('care_start');
            $table->json('medical_info')->nullable();
            $table->json('general_info')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('teamate_ccrezqs_dogs');
    }
}
