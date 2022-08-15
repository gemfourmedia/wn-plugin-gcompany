<?php namespace GemFourMedia\GCompany\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcompanyTestimonials extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcompany_testimonials', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('client_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->smallInteger('rating')->nullable()->default(5);
            $table->string('company', 255)->nullable();
            $table->string('webpage', 255)->nullable();
            $table->text('content');
            $table->string('reviewer_avatar', 255)->nullable();
            $table->string('reviewer_name', 255)->nullable();
            $table->string('reviewer_position', 255)->nullable();
            $table->boolean('published')->default(1);
            $table->boolean('featured')->default(0);
            $table->timestamp('published_at');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcompany_testimonials');
    }
}
