<?php namespace LukeTowers\EasyDonors\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLuketowersEasydonorsDonations extends Migration
{
    public function up()
    {
        Schema::create('luketowers_easydonors_donations', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('donor_id')->unsigned();
            $table->string('donor_name'); // NOTE: Original donor name stored here in case of changes to base donor record after the fact
            $table->dateTime('date')->nullable();
            $table->decimal('amount', 9, 2); // NOTE: Supports up to 9,999,999.99
            $table->string('currency')->default('CAD');
            $table->json('data')->nullable();
            $table->timestamps();
        });
        
        Schema::table('luketowers_easydonors_donations', function($table)
        {
	        $table->foreign('donor_id')
	        	->references('id')
	        	->on('luketowers_easydonors_donors');
	        });
    }
    
    public function down()
    {
        Schema::dropIfExists('luketowers_easydonors_donations');
    }
}
