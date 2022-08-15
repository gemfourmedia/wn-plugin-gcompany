<?php namespace GemFourMedia\GCompany\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcompanyCategories extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcompany_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('short_desc', 255)->nullable();
            $table->text('desc')->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('nest_left');
            $table->integer('nest_right');
            $table->integer('nest_depth')->default(0);
            $table->boolean('published')->default(1);
            $table->boolean('featured')->default(0);
            $table->string('group', 100);
            $table->text('params')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcompany_categories');
    }
}
