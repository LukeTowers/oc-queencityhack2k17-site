<?php namespace LukeTowers\EasyDonors\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLuketowersEasydonorsDonationAddresses extends Migration
{
    public function up()
    {
        Schema::create('luketowers_easydonors_donation_addresses', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('donation_id')->unsigned()->unique()->nullable();
            $table->string('address');
            $table->string('address_2')->nullable();
            $table->string('city');
            $table->string('province');
            $table->string('country');
            $table->string('postal_code');
            $table->json('data')->nullable();
            $table->timestamps();
        });
        
        Schema::table('luketowers_easydonors_donation_addresses', function($table)
        {
	        $table->foreign('donation_id')
	        	->references('id')
	        	->on('luketowers_easydonors_donations');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('luketowers_easydonors_donation_addresses');
    }
}
