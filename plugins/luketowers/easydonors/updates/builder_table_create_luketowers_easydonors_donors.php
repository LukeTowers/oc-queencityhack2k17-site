<?php namespace LukeTowers\EasyDonors\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLuketowersEasydonorsDonors extends Migration
{
    public function up()
    {
        Schema::create('luketowers_easydonors_donors', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->nullable()->unsigned();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('email')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
        
        Schema::table('luketowers_easydonors_donors', function($table)
        {
	        // References RainLab\User
	        $table->foreign('user_id')
	        	->references('id')
	        	->on('users');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('luketowers_easydonors_donors');
    }
}
