<?php namespace GemFourMedia\GCompany\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcompanyClients extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcompany_clients', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('desc')->nullable();
            $table->string('type', 30);
            $table->boolean('published')->default(1);
            $table->boolean('featured')->default(0);
            $table->string('logo', 255)->nullable();
            $table->string('link', 255)->nullable();
            $table->text('params')->nullable();
            $table->integer('sort_order')->default(1);
            $table->string('meta_title', 191)->nullable();
            $table->string('meta_description', 191)->nullable();
            $table->string('meta_keywords', 191)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcompany_clients');
    }
}
