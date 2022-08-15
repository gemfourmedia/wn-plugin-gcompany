<?php namespace GemFourMedia\GCompany\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcompanyArticlesFiles extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcompany_articles_files', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('article_id');
            $table->integer('file_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcompany_articles_files');
    }
}
