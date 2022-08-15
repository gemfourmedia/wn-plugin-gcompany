<?php namespace GemFourMedia\GCompany\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcompanyArticlesClients extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcompany_articles_clients', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('article_id');
            $table->integer('client_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcompany_articles_clients');
    }
}
