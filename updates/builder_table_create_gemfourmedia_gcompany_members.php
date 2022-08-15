<?php namespace GemFourMedia\GCompany\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcompanyMembers extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcompany_members', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255);
            $table->string('avatar', 255)->nullable();
            $table->text('quote')->nullable();
            $table->string('position', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->text('socials')->nullable();
            $table->string('department', 255)->nullable();
            $table->boolean('published')->default(1);
            $table->boolean('featured')->default(0);
            $table->text('skills')->nullable();
            $table->text('params')->nullable();
            $table->integer('sort_order')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcompany_members');
    }
}
