<?php namespace TeamAte\Ccrezqs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTeamateCcrezqsFosters extends Migration
{
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->string('foster_status')->default('registered');
            $table->json('contact_info')->nullable();
            $table->json('residence')->nullable();
            $table->json('personal_info')->nullable();
            $table->json('animal_info')->nullable();
            $table->json('foster_info')->nullable();
            $table->json('other_info')->nullable();
        });
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'foster_status')) {
            Schema::table('users', function($table)
            {
                $table->dropColumn('foster_status');
                $table->dropColumn('contact_info');
                $table->dropColumn('residence');
                $table->dropColumn('personal_info');
                $table->dropColumn('animal_info');
                $table->dropColumn('foster_info');
                $table->dropColumn('other_info');
            });
        }
    }
}
