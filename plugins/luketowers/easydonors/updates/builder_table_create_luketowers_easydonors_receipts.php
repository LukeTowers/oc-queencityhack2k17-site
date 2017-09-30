<?php namespace LukeTowers\EasyDonors\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLuketowersEasydonorsReceipts extends Migration
{
    public function up()
    {
        Schema::create('luketowers_easydonors_receipts', function($table)
        {
        	$table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('donation_id')->unsigned();
            $table->string('identifier');
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('luketowers_easydonors_receipts');
    }
}
