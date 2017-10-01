<?php namespace Look\Essentials\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLookEssentialsFeaturedImages extends Migration
{
    public function up()
    {
        Schema::create('look_essentials_featured_images', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('path')->nullable();
            $table->integer('owner_id');
            $table->string('owner_type');
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('look_essentials_featured_images');
    }
}
