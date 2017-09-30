<?php namespace LukeTowers\EasyDonors\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLuketowersEasydonorsDonorAddresses extends Migration
{
    public function up()
    {
        Schema::create('luketowers_easydonors_donor_addresses', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('donor_id')->unsigned()->nullable();
            $table->string('address');
            $table->string('address_2')->nullable();
            $table->string('city');
            $table->string('province');
            $table->string('country');
            $table->string('postal_code');
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('luketowers_easydonors_donor_addresses');
    }
}
