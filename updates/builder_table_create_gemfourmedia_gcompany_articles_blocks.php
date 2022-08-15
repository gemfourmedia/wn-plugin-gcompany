<?php namespace GemFourMedia\GCompany\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcompanyArticlesBlocks extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcompany_articles_blocks', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('article_id')->nullable()->unsigned();
            $table->string('title', 255)->nullable();
            $table->string('code', 255)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->text('content')->nullable();
            $table->boolean('published')->default(1);
            $table->text('params')->nullable();
            $table->integer('sort_order')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcompany_articles_blocks');
    }
}
