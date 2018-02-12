<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sources', function(Blueprint $table){
            $table->increments('id');
            $table->text('url');
            $table->longText('source');
            $table->integer('type_id')->default(0);
            $table->boolean('review')->default(false);
            $table->string('hash', 32)->unique();
            $table->timestamps();
            $table->integer('donor_id');
            $table->index('donor_id')
                ->references('id')->on('donors')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
