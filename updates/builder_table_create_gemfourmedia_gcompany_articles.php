<?php namespace GemFourMedia\GCompany\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcompanyArticles extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcompany_articles', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('category_id')->nullable();
            $table->string('title', 255);
            $table->string('code', 255);
            $table->string('group', 100);
            $table->string('subtitle', 255)->nullable();
            $table->text('introtext')->nullable();
            $table->text('content')->nullable();
            $table->text('embeds')->nullable();
            $table->text('params')->nullable();
            $table->integer('sort_order')->default(1);
            $table->boolean('published')->default(1);
            $table->boolean('featured')->default(0);
            $table->string('meta_title', 191)->nullable();
            $table->string('meta_description', 191)->nullable();
            $table->string('meta_keywords', 191)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcompany_articles');
    }
}
