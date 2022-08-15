<?php namespace GemFourMedia\GCompany\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcompanyFeedbacks extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcompany_feedbacks', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('form', 100)->nullable();
            $table->text('data')->nullable();
            $table->string('status', 100)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcompany_feedbacks');
    }
}
